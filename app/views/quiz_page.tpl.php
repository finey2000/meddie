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
<input type="radio" name="answer" value="{option_a}" /><span>{option_a}</span>
<input type="radio" name="answer" value="{option_b}"/><span>{option_b}</span>
<br/>
<input type="radio" name="answer" value="{option_c}" /><span>{option_c}</span>
<input type="radio" name="answer" value="{option_d}"/><span>{option_d}</span>
</p>
</section>

<div id='single_block'>
    <input type=text name=answer autocomplete=off placeholder="Your answer" />
</div>

<p>
<input type=submit value='submit' class='submit_button1' />
</p>
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
    <p></p>
    <div id='status' class="status_info"> <p>Please activate Javascript to continue with this quiz </p></div>

    <a href='#' id='show_actions'><img src='assets/images/button.png' alt='Actions' style="max-height: 20px;max-width: 50px;" /></a>

<section id='actions'> 
    <p></p>
<a href='<?php echo $current_result ?>' class='submit_button1' >Result</a> &nbsp; &nbsp;
<a href='<?php echo $reset_link?>'class='submit_button2' >Reset Quiz</a>
<p ><a href="<?php echo $user_link?>" class='submit_button2' ><?php echo $user_name?></a>
<a href="<?php echo $logout_link?>" class='submit_button1' >Logout</a></p>
</section>

</div>



</div>