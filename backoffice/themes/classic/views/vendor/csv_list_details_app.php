<?php
$this->renderPartial("/tpl/lazy_list",array(
 'back_link'=>isset($back_link)?$back_link:'',
 'data'=>'<input type="hidden" name="id" id="id"  value="'.$id.'">'
));
?>