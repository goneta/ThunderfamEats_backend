<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language;?>" dir="<?php echo Yii::app()->params['is_rtl']==true?'rtl':'ltr'?>" >
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<!-- ThunderfamEats redesign: site font (Rubik — closest free match to Uber Move) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500&display=swap" rel="stylesheet">
<!-- ThunderfamEats redesign: apply saved light/dark theme before first paint (no FOUC) -->
<script>(function(){try{var t=localStorage.getItem('tf-theme');if(t){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();</script>
<meta name="robots" content="index, follow" />
<meta name="<?php echo Yii::app()->request->csrfTokenName?>" content="<?php echo Yii::app()->request->csrfToken?>" />    
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/favicon-16x16.png">
<link rel="manifest" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/site.webmanifest">
<link rel="mask-icon" href="<?php echo Yii::app()->theme->baseUrl?>/assets/icons/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<?php $this->widget('application.components.WidgetFacebookPixel',array(
    'data'=>Yii::app()->params['settings']
));?>
</head>
<body class="position-relative <?php echo $this->getBodyClasses(); ?>" data-spy="scroll" data-target="#menu-category" data-offset="75" >

<?php echo $content; ?>

<!-- ThunderfamEats redesign: light/dark theme toggle handler -->
<script>
document.addEventListener('click', function (e) {
  var btn = e.target.closest ? e.target.closest('#tf-theme-toggle') : null;
  if (!btn) return;
  var cur = document.documentElement.getAttribute('data-theme');
  var next;
  if (cur === 'dark') { next = 'light'; }
  else if (cur === 'light') { next = 'dark'; }
  else { next = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'light' : 'dark'; }
  document.documentElement.setAttribute('data-theme', next);
  try { localStorage.setItem('tf-theme', next); } catch (err) {}
});
</script>

</body>
</html>