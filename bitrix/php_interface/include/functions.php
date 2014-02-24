<?
function dump3($var, $die = false, $all = false)
{
    global $USER;
    if( ($USER->GetID() == 1) || ($all == true))
    {
        ?>
        <pre style="font-size: 12px"><?print_r($var)?></pre><br>
    <?
    }
    if($die)
    {
        die;
    }
}


?>