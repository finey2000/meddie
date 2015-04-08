<header id='quiz_header'><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie" /></a>    
</header>
<div class="result_div">
    <h2 >Final Result</h2>
<table class="result_table">
<tr class="rt_row" ><td>Category</td><td>Total</td><td>Correct</td><td>Wrong</td><td>Score</td></tr>

<tr class="rt_row1" ><td>All</td><td><center><?php echo $total_questions ?></center></td><td><center><?php echo $total_correct_answers ?></center></td><td><center><?php echo $total_wrong_answers ?></center></td><td><center><?php echo $total_percentage_score ?>%</center></td></tr>

<?php foreach($sorted_results as $category => $cat_data ){?>
<tr class="rt_row2" ><td><?php echo $cat_data['category'] ?></td><td><center><?php echo $cat_data['total_count'] ?></center></td><td><center><?php echo $cat_data['right_count']?></center></td><td><center><?php echo $cat_data['wrong_count'] ?></center></td><td><center><?php echo $cat_data['percentage_score'] ?>%</center></td></tr>
<?php }?>
</table>

<p><a href="<?php echo $reset_link ?>" class="submit_button1" >Reset Quiz</a></p>

</div>