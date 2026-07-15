<h6><?php echo t("Cron link")?></h6>
<p class="text-muted mb-4"">
 <?php echo isset($message)?$message :t("below are the cron jobs that needed to run in your cpanel as http cron")?>
</p>

<?php foreach ($cron_link as $items):?>
<table class="table">
<tbody>
    <tr>
        <td>Link</td>
        <td><?php echo $items['link']?></td>
    </tr>
    <tr>
        <td>Note</td>
        <td><?php echo $items['label']?></td>
    </tr>
</tbody>
</table>
<?php endforeach;?>

<p>
    <a href="https://www.youtube.com/watch?v=7lrNECQ5bvM" target="_blank">
        <?php echo t("Click here how to run cron jobs")?>
    </a>
</p>