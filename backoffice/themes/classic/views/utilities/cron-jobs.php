<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$links,
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<div class="card">
  <div class="card-body">

     <?php foreach ($cron_link as $items):?>
     <h5><?php echo $items['title']?></h5> 
     <p class="text-muted mb-4"">
       <?=$items['description']?>
     </p>

     <?php foreach ($items['link'] as $items):?>
     <table class="table">
     <tbody>
         <tr>
            <td><?php echo t("Link")?></td>
            <td><?php echo $items['link']?></td>
         </tr>
         <tr>
            <td><?php echo t("Note")?></td>
            <td><?php echo $items['label']?></td>
         </tr>
     </tbody>
     </table>
      <?php endforeach;?>

     <?php endforeach;?>

   
    <p>
        <a href="https://www.youtube.com/watch?v=7lrNECQ5bvM" target="_blank">
            <?php echo t("Click here how to run cron jobs")?>
        </a>
    </p>

  <!-- </div> body -->
</div>
<!-- card -->