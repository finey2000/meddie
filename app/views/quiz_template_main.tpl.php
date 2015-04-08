<div id='quiz_status' class="status_info"> Status: {answered_questions}/{total_questions} ({questions_remaining} Questions Remaining).</div>
<div id='quiz_block'>
<h4>Question: {question_no}</h4>
<!-- display div for results here -->

<h2>LANGUAGE: {language}</h2>
<p><span class="question">{question}</span></p>
<h4>Your Answer</h4>
<div id='try_again' style="display: none">
<p></p>
</div>
<form method="post" action='{form_action}' class="form" onsubmit = "return validate_answer();" >
<!-- START multi_block -->
<p>
<input type="radio" name="answer" value="{option_a}" />{option_a}
<input type="radio" name="answer" value="{option_b}"/>{option_b}
<br/>
<input type="radio" name="answer" value="{option_c}" />{option_c}
<input type="radio" name="answer" value="{option_d}"/>{option_d}
</p>
<!-- END multi_block -->


<!-- START single_block -->
<input type=text name=answer autocomplete=off  />
<!-- END single_block -->

<input type=hidden id="pre_answer" value="{answer_value}" prefix="{prefix_value}" mtrial = "{multiple_trials}"/>
<input type=hidden name=cmd value=submit_answer />
<p>
<input type="submit" value="SUBMIT" class="submit_button2" />
</p>

</form>

</div>

<div id='answer_block'>
<p><center><span class="result">{result}</span></center></p>
<h4>Your Answer</h4>
<h2>{original_answer}</h2>
<h4>Original question</h4>
<span class="question">{original_question}</span>
<h4>Answer</h4>
<h1>{real_answer}</h1>
<h2>LANGUAGE: {language}</h2>
<h4>Function Syntax</h4>
<span style="color:#333333;font-weight:bolder" >{function_syntax}</span>
<p><a target=_blank href="{function_url}" class="link_button" >Learn More</a> 
<button class="button1" id='mark' onclick="return mark_function();" >{marked_function}</button>
</p>
<span class="status_info">Status: {answered_questions}/{total_questions} ({questions_remaining} Question(s) Remaining).</span>
<form method="post" action='{form_action}' ">
<input type=hidden name=cmd value={command} />
<p>
<input type="submit" value="{command_value}" class="submit_button2" />
</p>
</form>
</div>
<p><a href="{result_link}" class="submit_button1" >Current Result</a></p>