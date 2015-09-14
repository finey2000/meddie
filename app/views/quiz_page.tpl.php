<script src="<?php echo show_site('assets/js')?>/jquery-1.7.1.min.js"> </script>
<script src="<?php echo show_site('assets/js')?>/medara-quiz.js"> </script>
<script>
window.onload = function(){
//var answer = document.getElementById('answer');
	new Medara_quiz('quiz_main','<?php echo show_site()?>');
};
</script>
<header id='quiz_header'><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie" /></a>    
<br/><i>English word quiz</i>
</header>
<div id='quiz_main'>
<div id='question'></div>

<div id='answer'>
<form name='quiz_answer' id='quiz_answer'>
    <div id='notify_block' class='notify' > </div>

<section id='multi_block'>
<p>
<input type="button" class='submit_button1' name="answer" value="{option_a}" /> <br/>
<input type="button" class='submit_button1' name="answer" value="{option_b}"/> <br/>
<input type="button" class='submit_button1' name="answer" value="{option_c}" /> <br/>
<input type="button" class='submit_button1' name="answer" value="{option_d}"/> <br/>
</p>
</section>

<div id='single_block'>
    <input type=text name=answer autocomplete=off placeholder="Your answer" />
</div>

<div id="submitButton">
<input type=submit value='submit' class='submit_button1' />
</div>
</form>
</div>

<div id='result_block'> 
    <section>
    <div id='result_block_input'>
        <span id='input_answer'> </span> <br/>   
        <img id='result_img' />
       </div> 
    
    <div id='result_details'>
        <span id='real_answer'></span>
        <img id='audibly' src='<?=show_site("assets/images/speaker.png")?>' /><p></p>     
    <button id='mark_word' class='submit_button2' >Mark</button>    
    <button id='next_action' class='submit_button1'>Next Action</button>
    </div>
    <div style='clear: both;'></div>
    </section>
    <a href='#' id='show_examples'>Examples:</a>
    <section id='result_examples_sec'>        
        <ol id='result_examples'> </ol>
    </section>

</div>

<div id='quiz_misc' style='clear: both;'>
    <div id="previous-result"><button title='Show previous result'><=</button> </div>
    <div id='status' class="status_info"> <p>Please activate Javascript to continue with this quiz </p></div>

    <a href='#' id='show_actions'><img src='assets/images/button.png' alt='Actions' style="max-height: 20px;max-width: 50px;" /></a>

<section id='actions'> 
<a href='<?=$settingsLink?>' class='submit_button2' title='Change current game settings' >Settings</a>     
<a href='<?=$current_result?>' title='See current game results' class='submit_button1' >Result</a> 
<a href='<?=$reset_link?>'class='submit_button2' >Reset Quiz</a>
<a href="<?=$user_link?>" class='submit_button2' ><?php echo $user_name?></a>
<a href="<?=$logout_link?>" class='submit_button1' >Logout</a>
</section>

</div>



</div>