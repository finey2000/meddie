
function Medara_quiz(quiz_body_id,site_url)
{
    		Medara_quiz.prototype.site_url = site_url;
                Medara_quiz.prototype.navigate_src = ''; 
                Medara_quiz.prototype.correct_img_src = this.site_url+'assets/images/correct_sign.png';
                Medara_quiz.prototype.wrong_img_src = this.site_url+'assets/images/wrong_sign.jpeg';
                Medara_quiz.prototype.audibly_word = '';//contains the current word that'll be read alive.
                Medara_quiz.prototype.fullscreen = false;
		Medara_quiz.prototype.answered_ques = new Array();
                Medara_quiz.prototype.quiz_subjects = new Array();
                Medara_quiz.prototype.current_request_ids = [];
                Medara_quiz.prototype.all_asked_questions = new Array();
                Medara_quiz.prototype.new_questions_store = new Array();
		Medara_quiz.prototype.available_ques = new Array();
                Medara_quiz.prototype.latest_ques = [];
		Medara_quiz.prototype.sync_success = false;
		Medara_quiz.prototype.submit_anyway = false;                
		Medara_quiz.prototype.load_failure = false;
		Medara_quiz.prototype.load_interval_id = null;
		Medara_quiz.prototype.questions_per_load = 4;
                Medara_quiz.prototype.sync_on = 2; //defines when sync should be performed (i.e how many questions should be left)
                Medara_quiz.prototype.sync_timeout_id;
		Medara_quiz.prototype.multiple_trials = 3;
                Medara_quiz.prototype.trials_count = this.multiple_trials;
		Medara_quiz.prototype.current_question = {};
		Medara_quiz.prototype.prev_ques = {};        
                Medara_quiz.prototype.template = null;
		Medara_quiz.prototype.quiz_page = {'page_id': quiz_body_id,
                    //set page blocks
                                              'whole_body' : $('#'+quiz_body_id),
                                                'quiz_status' : $('#status'),
                                                'question_block' : $('#question'),
                                                  'answer_block' : $('#answer'),
                                                    'notify_block': $('#notify_block'),
                                                    'result_block' : $('#result_block'),
                                                    'result_img' : $('#result_img'),
                                                            'next_action' : $('#next_action'),
                                                           'result_details': $('#result_details'),
                                                           'actions_div': $('#actions'),
                                                           'examples_section':$('#result_examples_sec')
                                                     };
                Medara_quiz.prototype.quiz_status = {
			'multi_mode':false,
			'game_over': false,
                        'more_questions':true, //server uses this var to inform if there are more questions waiting to be loaded
			's_answered_questions': 0,
			's_total_questions':0,
			's_questions_remaining':0,
                        'j_answered_questions':0, //count of answered questions from front end
                        'j_questions_remaining':0, //count of remaining questions from front end
                        'j_total_questions':0,    //count of total questions from front end
                        'current_question':0,
			'allow_multiple_trials':false,
                        'next_action':null,
                        'autospeak': false,
			'autonew': false
		};
                Medara_quiz.prototype.sync_success_action = null;
                Medara_quiz.prototype.sync_failure_action = null;
                Medara_quiz.prototype.posted_answered_questions = null;
                        
	


	this.quiz_page.quiz_status.html('Quiz Loading....Please Wait');
	//register submit handler for submit event
        $('#quiz_answer').submit($.proxy(this.submit_answer,this));
        //register click handler for next action
        this.quiz_page.next_action.click($.proxy(this.next_action,this));      
        $('#previous-result').click($.proxy(this.showPreviousResult,this));      //click handler for showing previous results
      //set html and set word marking handler
      $('#mark_word').click($.proxy(this.mark_previous,this));        
        //register click handler for show actions
        $('#show_actions').click($.proxy(function(){this.quiz_page.actions_div.toggle();},this));
        //register click handler for show examples
        $('#show_examples').click($.proxy(function(){this.quiz_page.examples_section.toggle();},this));

        //set up audible reading
        if(window.speechSynthesis){
            $('#audibly').click($.proxy(this.speak_aloud,this));
            //set key handler for text aloud
            $(document).keydown(function(key){
                if((key.key == 'Enter' || key.keyCode=='13') && key.ctrlKey) $('#audibly').trigger('click');
            });
        }else{
            $('#audibly').remove();
        }
        
    //set key handler for submit anyway button
    $(document).keydown($.proxy(function(key){       
        if((key.key == 'Enter' || key.keyCode=='13') && key.shiftKey) this.submit_anyway = true;
    },this));        
 
        //get quiz subject ids
        jQuery.get(this.site_url+'get-subjects',{},$.proxy(this.load_subjects,this));
	
}



/**
 * uses loaded quiz data to prepare quiz for display
 */
Medara_quiz.prototype.prepare_quiz_data = function()
{
    //load front end status data
    this.quiz_status.j_total_questions = this.quiz_status.s_total_questions;
    this.quiz_status.j_answered_questions = this.quiz_status.s_answered_questions;
    this.quiz_status.j_questions_remaining = this.quiz_status.s_questions_remaining;
        //remove multi or single answer block according to quiz setting
        this.quiz_page.single_answer_block = $('#single_block');
	this.quiz_page.multi_answer_block = $('#multi_block');
        //modify interface if its in multimode
        if(this.quiz_status.multi_mode){
        this.quiz_page.single_answer_block.hide();            
        $('#submitButton').hide();
            //set click handler
        this.quiz_page.multi_answer_block.find('input').click($.proxy(this.submit_multiple,this));
        } else this.quiz_page.multi_answer_block.remove();
        
        //register click handler for all anchor elements
        this.register_anchors();
        //prepare to display question
	this.prepare_next_question();
	this.update_game_status();
	this.display_question();
	this.display_answer_div();
};

/**
 * Registers a click handler on all the http anchors in this page to
 * ensure that quiz is synced to the server before navigation begins
 * @returns {undefined}
 */
Medara_quiz.prototype.register_anchors = function(){
  var real_anchors = $('a').not(function(index,element){
     if(element.href.split('#')[0] === document.URL.split('#')[0])return true;     
  });
  real_anchors.click($.proxy(this.navigate_anchor,this));
};

/**
 * Receives a click event on an anchor and 
 * navigates to the requested page after results have been synced
 * @param {type} event
 * @returns {undefined}
 */
Medara_quiz.prototype.navigate_anchor = function(event)
{
    event.preventDefault();
    this.navigate_src = event.currentTarget.href;
    this.navigate_to();
};

/**
 * Prepares the next question object
 * @returns {undefined}
 */
Medara_quiz.prototype.prepare_next_question = function()
{
    //check for and add latest questions to the list of available questions
     this.set_next_questions();
    //make sure available questions have at least one element
    if(this.available_ques.length < 1) return false;

    //extract the first question from available questions array
    this.current_question = this.available_ques.shift();
    //push current question into all asked questions array
    this.all_asked_questions.push(this.current_question);
    //update quiz status    
    this.quiz_status.current_question = this.quiz_status.j_answered_questions + 1;  
    this.quiz_status.j_questions_remaining--; 
    return true;   
};

/**
 * Loads more questions either from the new questions store or from the latest questions array 
 * or from the server
 * @returns {undefined}
 */
Medara_quiz.prototype.set_next_questions = function()
{
        if(this.latest_ques) {
            //push each new member of latest questions array into our new_questions store        
      this.latest_ques.forEach($.proxy(function(value,index){
       this.new_questions_store.push(value);
              },this));
              this.latest_ques = [];    
    }
    if(this.available_ques.length === 0){
        //move new questions store to available questions array
        this.available_ques = this.new_questions_store;
        //empty new_questions store
        this.new_questions_store = new Array();
    }
    var all_available_questions = this.new_questions_store.length + this.available_ques.length;
    //define when to sync and download new questions from the server
    if((all_available_questions <= this.sync_on) && this.quiz_status.more_questions){ 
            this.sync_quiz_data();
        }
        
};

Medara_quiz.prototype.get_all_available_questions = function()
{
  return this.available_ques.concat(this.new_questions_store,this.latest_ques);  
};

Medara_quiz.prototype.get_object_ids = function(array_values)
{
    var ids = [];
    var length = array_values.length;
    var i = 0;
    array_values.forEach(function(value){
        ids[i] = value.id;
        i++;
    });
    return ids;
}

/**
 * Tries to update quiz data after a certain time
 * @returns {undefined}
 */
Medara_quiz.prototype.update_sync_status = function()
{
    if(this.quiz_status.j_answered_questions > this.quiz_status.s_answered_questions){        
      this.sync_quiz_data();
    }
};

Medara_quiz.prototype.set_sync_time = function()
{            //set timeout to syncronise quiz data
    if(this.sync_timeout_id){
        clearTimeout(this.sync_timeout_id);
    }
      this.sync_timeout_id =  setTimeout($.proxy(this.update_sync_status,this),10000);
};

/**
 * displays quiz status
 */
Medara_quiz.prototype.update_game_status = function()
{	
    var status = this.quiz_status.current_question+'/'+this.quiz_status.j_total_questions+'('+this.quiz_status.j_questions_remaining+' Question(s) Remaining).';    
			this.quiz_page.quiz_status.html(status);
};


Medara_quiz.prototype.display_question = function()
{
	if(! this.current_question) return false;
	var q_object = this.current_question;
	var question = q_object.question;
	var question_block = this.quiz_page.question_block;	
	question_block.html(question);
	question_block.show();
};



Medara_quiz.prototype.display_answer_div = function()
{
	var answer_block = this.quiz_page.answer_block;
	var q_object = this.current_question;
	if(this.quiz_status.multi_mode){
                //prepare multiple answers and populate answer
		var inputs = this.quiz_page.multi_answer_block.find('input');
		//var values = this.quiz_page.multi_answer_block.find('span');
		for(var i=0; i<inputs.length; i++)
			{
                            
			inputs[i].value = q_object.options[i];
			//values[i].innerHTML = q_object.options[i];
			}
	}
        //display
	answer_block.show();
        //focus keyboard input on single input field
        if(!this.quiz_status.multi_mode) $('#single_block input').focus();
        
};

Medara_quiz.prototype.display_load_failure = function()
{
	if(this.load_interval_id){
		window.clearInterval(this.load_interval_id);
		this.load_interval_id = null;
	}
};


/**
 * loads all the quiz subject ids gotten from the server by ajax
 * @type type
 */
Medara_quiz.prototype.load_subjects = function(server_data)
{
    	this.quiz_subjects = JSON.parse(server_data);
    //load and sync the rest of the data from the server
        this.sync_success_action = this.prepare_quiz_data;
        this.sync_failure_action = this.display_load_failure;
	this.sync_quiz_data();
};

/**
 * returns an array of subject ids that will bu used to request data from the server
 * @returns {Array} subjects
 */
Medara_quiz.prototype.get_request_ids = function()
{
    if(this.quiz_subjects.length === 0) return [];
    var subjects = [];
    var limit = this.questions_per_load;
    if(limit > this.quiz_subjects.length) limit = this.quiz_subjects.length;
    for(var i=0;i<limit;i++){
        subjects[i] = this.quiz_subjects.shift();
    }
    return subjects;
};


/**
 * Uses ajax to sync and load quiz data as a json file format
 * @param1 {Function} success_action
 * @param2 {Function} failure_action
 */
Medara_quiz.prototype.sync_quiz_data = function()
{    
	this.posted_answered_questions = this.answered_ques;
        this.answered_ques = [];
                 this.current_request_ids = this.get_request_ids();       
	var post_data = JSON.stringify({'answered_ques':this.posted_answered_questions,'request_ids':this.current_request_ids});
	jQuery.post(this.site_url+'sync-quiz-data',{'quiz_data':post_data},$.proxy(this.handle_sync_success,this));
	
};


Medara_quiz.prototype.handle_sync_failure = function(){
		this.load_failure = true;
                
                //take failure action if defined
                if(this.sync_failure_action) this.sync_failure_action();
	};
        
/**
 * receives and processes new data received from the server via jQ post
 * @param {JSON string} new_data
 * @returns {undefined}
 */
Medara_quiz.prototype.handle_sync_success = function(new_data){
         //console.log(new_data);            

		var new_quiz_data = JSON.parse(new_data);
		this.latest_ques = new_quiz_data.new_questions;                
		var s_quiz_status = new_quiz_data.quiz_status;
                this.posted_answered_questions = [];
                this.current_request_ids = [];
		//sync quiz status
		this.quiz_status.game_over = s_quiz_status.game_over;
		this.quiz_status.multi_mode = s_quiz_status.multi_mode;
		this.quiz_status.allow_multiple_trials = s_quiz_status.allow_multiple_trials;
                this.quiz_status.autospeak = s_quiz_status.autospeak;
				this.quiz_status.autonew = s_quiz_status.autonew;                
               // this.quiz_status.more_questions = s_quiz_status.more_questions; //boolean that tells if there are more questions on the server side
		//if the game is yet to be over
		if(! this.quiz_status.game_over){
			this.quiz_status.s_total_questions = s_quiz_status.total_questions; //total available questions from server side
			this.quiz_status.s_answered_questions = s_quiz_status.answered_questions; //total answered questions saved by server
			this.quiz_status.s_questions_remaining = s_quiz_status.questions_remaining; //total questions remaining on server side
		}
		this.sync_success = true;
                //take success action if defined
                if(this.sync_success_action){
                    var success_action = $.proxy(this.sync_success_action,this);
                    this.sync_success_action = null;
                    success_action();
                }
		
	};


/**
 * Notifies the user that he entered the wrong answer
 * if multiple trials are enabled
 * @returns {undefined}
 */
Medara_quiz.prototype.notify_wrong_answer = function()
{
    var remaining_trials = --this.trials_count;
    this.quiz_page.notify_block.html('Incorrect Answer: Please try again. '+remaining_trials+' trials remaining.<strong> <a href="#" id="submit_anyway_link">submit anyway</a></strong>').show();
    $('#submit_anyway_link').click({'submit_anyway':true},$.proxy(this.submit_answer,this));
};
/**
 * receives submitted answer
 * @param {jQuery} event object
 */
Medara_quiz.prototype.submit_answer = function(event,answer)
{	
    event.preventDefault();    
    //check if submit_anyway data has been passed
    var submit_anyway = false;
    if(this.submit_anyway) submit_anyway = this.submit_anyway;     
    else if(event.data && event.data.submit_anyway) submit_anyway = event.data.submit_anyway;  
    this.submit_anyway = false;
    
	if(!answer) answer = this.get_answer().trim();
	//check that input has been entered
	if(!answer && !submit_anyway){
		//this.notify_answer('You must Enter A Value to proceed');
                this.quiz_page.notify_block.text('You must Enter A Value to proceed').show();
		return;
	}
        if(!answer) answer = '=empty='; //define answer in case it was not provided
	var real_answer = this.current_question.answer.trim();	
	//validate answer       
	if((answer.toLowerCase() !== real_answer.toLowerCase()) && this.quiz_status.allow_multiple_trials && this.trials_count && !(submit_anyway))
		{			
                    this.notify_wrong_answer();
			return;
		}else{ 	//process answer');
			this.process_answer(answer);
                        this.clear_answer_form();
                        this.trials_count = this.multiple_trials;
			return;
		}	

	
};


/**
 * used for submitting answe in multimode
 * @param {type} event
 * @returns {undefined}
 */
Medara_quiz.prototype.submit_multiple = function(event){
    var button = $(event.target)
    //console.log($(event.target).val());
    button.removeClass('submit_button1').addClass('submit_button2');
    this.submit_answer(event,button.val());
}

/**
 * processes and answer and display right or wrong
 * @param {string} answer string containing submitten answer
 */
Medara_quiz.prototype.process_answer = function(answer)
{
	//hide answer block
        this.quiz_page.answer_block.toggle();
        //determine if answer is correct
        var cur_ques = this.current_question;
        var grading;
        if(cur_ques.answer.trim().toLowerCase() === answer.toLowerCase())
        { grading = true; } //correct   
        else{ grading = false;}    
         var prev_ques = this.prev_ques = {id:cur_ques.id,grade:grading,marked:cur_ques.marked,question:cur_ques.question};
        //push current question into answered questions and delete current question
        this.answered_ques.push(prev_ques);       
        //update number of answered questions on status object
        this.quiz_status.j_answered_questions++;
         //reset sync status
         this.sync_success = false;
         //set sync time
        this.set_sync_time();
       
        //populate result details for display
        this.populate_result_details(answer,cur_ques);
        //this.quiz_page.result_details.hide();
        //set audibly word
        this.audibly_word = cur_ques.answer;
        //if autospeak is enabled, pronouce word
        if(this.quiz_status.autospeak) this.speak_aloud(1);        
            //define next action to be set
            if(this.more_questions()){
                this.quiz_status.next_action = 'question';
                this.quiz_page.next_action.html('Next');
                //prepare next question
                this.prepare_next_question();  
                //if autonew is set, display new question after a timeout
                if(this.quiz_status.autonew) setTimeout($.proxy(this.next_action,this),1000);                
            }else{
                this.quiz_status.next_action = 'result';
                this.sync_quiz_data();
                this.quiz_page.next_action.html('View Results');
            }
            //update quiz status here
            
            //display result
        $('#previous-result').hide();            
 this.quiz_page.result_block.show(); 
this.quiz_page.next_action.focus();
  };
  
   /**
   * Reads aloud the last answer
   * @returns {undefined}
   */
  Medara_quiz.prototype.speak_aloud = function(times)
  {
      if(!window.speechSynthesis) return;
      var word = new SpeechSynthesisUtterance(this.audibly_word);
      if(typeof times != 'number') times = 1;            
      while(times > 0){
      window.speechSynthesis.speak(word);
      times--;
        }
  };
  
  /**
   * toggles the mark status of the last asked question
   * @returns {undefined}Marks 
   */
  Medara_quiz.prototype.mark_previous = function()
  {
      var url = this.site_url+'quiz/toggle-mark/'+this.prev_ques.id; 
      //function to confirm mmark request
      var confirm_mark = function(data){
          var confirm = JSON.parse(data);
          if(confirm === true){
              
       var mark;
      if(this.prev_ques.marked){
          this.prev_ques.marked = false;
          mark = 'Mark';
      }else{
          this.prev_ques.marked = true;
          mark = 'Unmark';
      }
      //set html
      $('#mark_word').html(mark);
  }
      };
      //make http mark request
      $.get(url,'',$.proxy(confirm_mark,this));

  };
  
  /**
   * Populates the result details div with the the details of the previous question
   * @param {string} answer
   * * @param {string} correct_answer
   * @returns {undefined}
   */
  Medara_quiz.prototype.populate_result_details = function(answer,question)
  { 
      //enter answers
      $('#input_answer').text(answer);
      $('#real_answer').text(question.answer);
      if(this.prev_ques.grade)//if correct
      {
          this.quiz_page.result_img.attr('src',this.correct_img_src);    
      }else{ 
          this.quiz_page.result_img.attr('src',this.wrong_img_src);     
      }

      var mark;
      if(this.prev_ques.marked){
          mark = 'Unmark';
      }else{
          mark = 'Mark';
      }
      //set mark html
      $('#mark_word').html(mark);
      //populate examples area
      if(question.examples){
          $('#show_examples').show();
          //split examples into an array
          var examples = question.examples.split('::');
          var examples_str = '';
          examples.forEach(function(value){
             examples_str = examples_str+'<li>'+value+'</li>'; 
          });
          $('#result_examples').html(examples_str);
      }else{
          $('#show_examples').hide();
      }
  };
  
  Medara_quiz.prototype.display_grade_image = function(grade)
  {
      //if grade is true(correct display correct image and hide 
     // if(grade)
  };
  
  /**
   * Ann event handler that launches the next action as requested by 
   * user clicking the next action button
   * @returns {undefined}
   */
  Medara_quiz.prototype.next_action = function(){
      if(this.quiz_status.next_action === 'question')
      {
          //display next question
          //hide result div
          
        this.quiz_page.examples_section.hide();
        this.quiz_page.result_block.hide();
        this.quiz_page.notify_block.html('').hide();
        this.update_game_status();
        //return colors if in multimode
        if(this.quiz_status.multi_mode) this.quiz_page.multi_answer_block.find('input').removeClass('submit_button2').addClass('submit_button1');
	this.display_question();
	this.display_answer_div();
        $('#previous-result').show();
        
      }
            if(this.quiz_status.next_action === 'result')
      {
          //navigate to result page
          this.navigate_src = this.site_url+'final-result';
          this.navigate_to();
      }
  };
  
  /**
   * Navigates to the requested page after syncronising results
   */
Medara_quiz.prototype.navigate_to = function()
{
    if(!this.navigate_src) return false;
     if(this.sync_success){
          document.location = this.navigate_src;
      }else{
          //notify sync status and reload later
          this.quiz_page.quiz_status.html('Still Syncronising Results...please wait');
          setTimeout($.proxy(this.navigate_to,this),5000);
      }
};
  
/**
 * Finds out if there are unanswered questions still available
 * @returns {boolean}
 */
Medara_quiz.prototype.more_questions = function(){        
    var available_questions = this.available_ques.length+this.new_questions_store.length+this.latest_ques.length;
    if(available_questions >= 1) return true;
    else return false;
};



/**
 * gets and returns the user's submitted answer
 */
Medara_quiz.prototype.get_answer = function()
{
	var form = document.forms[0];
	if(!form.answer.length){ //if its not an array
		if(form.answer.value){
                    return form.answer.value;
                }else{
                    return '';
                }
	}else{
		for(var i=0; i<form.answer.length; i++){
			if(form.answer[i].checked){
                            return form.answer[i].value;
                        }
		}		
       return '';
	}
};

/**
 * clears the user's earlier submitted answer
 */
Medara_quiz.prototype.clear_answer_form = function()
{
	var form = document.forms[0];
	if(!form.answer.length && form.answer.value){ //if its not an array
		form.answer.value = '';
	}else{
		for(var i=0; i<form.answer.length; i++){
			if(form.answer[i].checked)  form.answer[i].checked = false;                                                   
		}		
	}
};

/**
 * redisplays last quiz result as activated by user
 * @returns {undefined}
 */
Medara_quiz.prototype.showPreviousResult = function(){
	//hide answer block
        this.quiz_page.answer_block.toggle();    
        $('#previous-result').hide();      
        this.quiz_page.question_block.html(this.prev_ques.question);
        this.quiz_page.result_block.show(); 
        this.quiz_page.next_action.focus();
};