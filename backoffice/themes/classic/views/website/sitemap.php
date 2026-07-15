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
    <h2><?php echo $this->pageTitle?></h2>
    <p class="m-0"><?php echo t("This is an xml Sitemap generated, mean to be consume by search engines like Google or bing.")?></p>
    <p><?php echo t("You can find more information on XML sitemaps at sitemaps.org")?></p>

    <h6 class="mb-1 mt-4"><?php echo t("Your sitemap URL")?></h6>
     <div class="bg-light p-2 pl-3 pr-3 rounded" style="display:inline-block;">
        <div>
            <a href="<?php echo $sitemap_url?>" target="_blank"><?php echo $sitemap_url?></a>
        </div>
     </div>

    </div>
  </div>
</div>