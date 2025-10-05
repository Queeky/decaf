<?php 
use Illuminate\Support\Facades\Log;

$hostFormData = function() { ?>
    <label for='host-title'>Set Story Title:</label> 
    <input type='text' name='host-title'> 
    <label for='starter-text'>Begin the story:</label>
    <textarea name='starter-text'>Once upon a time...</textarea> 
    <label for='host-limit'>Set Word Limit:</label> 
    <input type='number' name='host-limit' min='1' value='3'> 
    <input type='hidden' name='session' value='<?php echo session("SESSION_ID"); ?>'> 
    <button type='submit'>Submit</button> 
<?php }
?>