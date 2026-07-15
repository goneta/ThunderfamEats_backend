<div id="vue-home-widgets" class="container p-2" style="min-height: calc(65vh)" >

<components-featured-merchant
title="<?php echo isset($title)?$title:''?>"  
:is_filters="true"   
>
</components-featured-merchant>

</div>

<?php $this->renderPartial("//components/template_restaurant_list");?>