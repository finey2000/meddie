<h3>Modify Current Game Settings</h3>
<form method="post" action='<?= $formAction ?>' name='gsettings' >
<section>
<h4>Quiz Mode</h4>
<input type="radio" name="mode" value="2" <?=$multiModeTrue?> />Single Question Style
<input type="radio" name="mode" value="1" <?=$multiModeFalse?> />Multi-Choice Questions
</section>

<section>
<h4>Allow Multiple Trials</h4>
<input type="radio" name="mtrials" value="1" <?=$multiTriesTrue?> />Yes
<input type="radio" name="mtrials" value="0" <?=$multiTriesFalse?> />No
</section> 

<section>
<h4 title="Automatically pronounce words after results disclosure">Allow Auto Speak</h4>
<input type="radio" name="autospeak" value="1" <?=$autospeakTrue?> />Yes
<input type="radio" name="autospeak" value="0" <?=$autospeakFalse?> />No
</section>

<section>
<h4 title="Automatically display a new question after the answer of a previous question has been displayed">Autoload New Questions</h4>
<input type="radio" name="autonew" value="1"  <?=$autonewTrue?> />Yes
<input type="radio" name="autonew" value="0" <?=$autonewFalse?> />No
</section>


<input type=hidden name=cmd value=gsettings />
<p><input type="submit" title='Save and return to game' value="SAVE" class="submit_button2" /> <a href='<?=$gameLink?>' class='submit_button1' >Cancel</a></p>
</form>