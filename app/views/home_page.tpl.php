<script src="<?php echo show_site('assets/js')?>/jquery-1.7.1.min.js"> </script>

<script> 

window.onload = function()
{
          var sel_cat = document.getElementById('sel_cat');
           var source_qs = document.getElementById('source_qs');

           if(sel_cat && source_qs){
            jQuery('#key_category').css('display','none');                      
           }
      
}

function toggle_category()
{
	//console.log('displayed');
	 var sel_cat = document.getElementById('sel_cat');
	 if(sel_cat.checked) jQuery('#key_category').css('display','block');
	 else jQuery('#key_category').css('display','none');
	
}


function validate_form()
{
	var form = document.forms[0];
	var langs = form.getElementsByClassName('lang');
	var selected = 0;
	var sel_cat = document.getElementById('sel_cat');
	if(sel_cat.checked)
	{
		//validate language selection if only its been checked
	for(var i=0; i<langs.length;i++)
    if(langs[i].checked) selected++;              

	if(!selected){
		 alert('You must select at least one language to proceed');
		 return false;
	            }
	}
	return true;
}
</script>

<header ><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie"/></a> <br/> 
<i>English word quiz</i>
</header>

<!-- START error_block -->

<!-- error msg goes here -->
<?php form_error()?>

<!-- END error_block -->
<form method="post" action='<?php echo $form_action ?>' name='start_form' >

<?php if($marked_functions){?>
    <section id='source_qs' onchange="toggle_category();" >
<h4>Select Source Questions:</h4>
<input type="radio" name="source" value="1" id='sel_cat' />All Available
<input type="radio" name="source" value="3" CHECKED />All Marked (<?php echo $marked_functions ?>)
<input type="radio" name="source" value="2" />All Unmarked (<?php echo $unmarked_functions ?>)
</section>
<?php }else{?>
<input type="hidden" name="source" value="1" id='sel_cat' CHECKED />
<?php }?>

<!-- START category div -->
<section id='key_category'  >
<h4>Select Category</h4>
<select name='category' class='category' >
<option selected value=0 >All categories</option>
<?php foreach($categories as $category) {?>
<option value='<?php echo $category['id']?>' > <?php echo $category['name']?> (<?php echo $category['count']?>)</option>
<?php }?>
<!-- END category -->
</select>
</section>
<!-- END category div -->

<section>
<h4>Quiz Mode</h4>
<input type="radio" name="mode" value="2" CHECKED />Single Question Style
<input type="radio" name="mode" value="1" />Multi-Choice Questions
</section>

<section>
<h4>Allow Multiple Trials</h4>
<input type="radio" name="mtrials" value="1" CHECKED />Yes
<input type="radio" name="mtrials" value="0" />No
</section> 

<section>
<h4>Questions</h4>
<select name="questions">
<option selected value='<?php echo $all_value ?>' >All Questions </option>
<option value=200>200 Questions</option>
<option value=100>100 Questions</option>
<option value=50>50 Questions</option>
<option value=20>20 Questions</option>
<option value=5>5 Questions</option>
</select>
</section>

<input type=hidden name=cmd value=startGame />
<p><input type="submit" value="START" class="submit_button2" /> <a href='<?= show_site('user')?>' class='submit_button1' >User</a></p>
</form>

