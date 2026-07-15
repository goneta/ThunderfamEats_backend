<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* __string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc */
class __TwigTemplate_605651d859d06e76a0227184775ccd69847949fdfb688862ee669f231da5f127 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        $this->loadTemplate("header.html", "__string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc", 1)->display($context);
        // line 2
        echo "<table style=\"width:100%;\">
 <tbody><tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    <img style=\"max-width:20%;max-height:50px;\" src=\"";
        // line 5
        echo twig_escape_filter($this->env, ($context["logo"] ?? null), "html", null, true);
        echo "\">
  </td>
 </tr>
 <tr>
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"left\">
    
   <table width=\"50%\" align=\"center\">
   <tbody><tr>
    <td>
\t
\t<p style=\"margin-bottom:10px;\">Hi ";
        // line 15
        echo twig_escape_filter($this->env, ($context["restaurant_name"] ?? null), "html", null, true);
        echo ",</p>
\t
\t <p style=\"margin-bottom:10px;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus quis nunc ut metus vulputate imperdiet at eget ipsum. Duis pharetra eros nec purus auctor, ut dapibus nunc convallis. Phasellus pellentesque lorem eros, et molestie velit pulvinar eget. Praesent orci orci, pulvinar ac nisi sit amet, cursus imperdiet mauris. Sed pharetra, nibh non maximus blandit, ex felis sagittis turpis, et porttitor dui nibh a eros. Donec imperdiet non ex molestie consequat. Duis posuere tortor eget nibh imperdiet sollicitudin. Curabitur porta placerat ex, vitae consequat turpis semper in. Integer non nulla justo. Phasellus posuere faucibus erat, ac ornare odio suscipit sed. Cras et erat dui. </p>\t\t
\t
\t</td>
   </tr>
   </tbody></table>
\t
   </td>
 </tr>
 
 
 
  
 <tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    
   <table style=\"width:100%; table-layout: fixed;\">
\t  <tbody><tr>
\t    <th colspan=\"3\" style=\"text-align: left;\"><h5>Contact Us</h5></th>
\t    <th colspan=\"7\" style=\"text-align: left;\"><h5>For  promos, news, and updates, follow us on:</h5></th>
\t  </tr>
\t  <tr>
\t    <td colspan=\"3\" style=\"text-align: left; padding:0 3px;\" valign=\"top\">
\t     <p>";
        // line 39
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 39), "html", null, true);
        echo "</p>
         <p>";
        // line 40
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 40), "html", null, true);
        echo "</p>
         <p>";
        // line 41
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 41), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t      ";
        // line 44
        $this->loadTemplate("social_link.html", "__string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc", 44)->display($context);
        // line 45
        echo "\t     
\t     <table>
\t      <tbody><tr>
\t      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Terms and Conditions</a></td>
\t      <td>●</td>
\t      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Privacy Policy</a></td>
\t      </tr>
\t     </tbody></table>
\t    
\t    </td>
\t  </tr>
\t</tbody></table>
  
  </td>
 </tr>
 
</tbody></table>
";
        // line 62
        $this->loadTemplate("footer.html", "__string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc", 62)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  119 => 62,  100 => 45,  98 => 44,  92 => 41,  88 => 40,  84 => 39,  57 => 15,  44 => 5,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% include 'header.html' %}
<table style=\"width:100%;\">
 <tbody><tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    <img style=\"max-width:20%;max-height:50px;\" src=\"{{logo}}\">
  </td>
 </tr>
 <tr>
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"left\">
    
   <table width=\"50%\" align=\"center\">
   <tbody><tr>
    <td>
\t
\t<p style=\"margin-bottom:10px;\">Hi {{restaurant_name}},</p>
\t
\t <p style=\"margin-bottom:10px;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus quis nunc ut metus vulputate imperdiet at eget ipsum. Duis pharetra eros nec purus auctor, ut dapibus nunc convallis. Phasellus pellentesque lorem eros, et molestie velit pulvinar eget. Praesent orci orci, pulvinar ac nisi sit amet, cursus imperdiet mauris. Sed pharetra, nibh non maximus blandit, ex felis sagittis turpis, et porttitor dui nibh a eros. Donec imperdiet non ex molestie consequat. Duis posuere tortor eget nibh imperdiet sollicitudin. Curabitur porta placerat ex, vitae consequat turpis semper in. Integer non nulla justo. Phasellus posuere faucibus erat, ac ornare odio suscipit sed. Cras et erat dui. </p>\t\t
\t
\t</td>
   </tr>
   </tbody></table>
\t
   </td>
 </tr>
 
 
 
  
 <tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    
   <table style=\"width:100%; table-layout: fixed;\">
\t  <tbody><tr>
\t    <th colspan=\"3\" style=\"text-align: left;\"><h5>Contact Us</h5></th>
\t    <th colspan=\"7\" style=\"text-align: left;\"><h5>For  promos, news, and updates, follow us on:</h5></th>
\t  </tr>
\t  <tr>
\t    <td colspan=\"3\" style=\"text-align: left; padding:0 3px;\" valign=\"top\">
\t     <p>{{site.address}}</p>
         <p>{{site.contact}}</p>
         <p>{{site.email}}</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t      {% include 'social_link.html' %}
\t     
\t     <table>
\t      <tbody><tr>
\t      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Terms and Conditions</a></td>
\t      <td>●</td>
\t      <td style=\"padding:0;\"><a href=\"#\" style=\"color:#000;font-size:16px;\">Privacy Policy</a></td>
\t      </tr>
\t     </tbody></table>
\t    
\t    </td>
\t  </tr>
\t</tbody></table>
  
  </td>
 </tr>
 
</tbody></table>
{% include 'footer.html' %}
", "__string_template__0ebf1bf8a23ce2cf3b1af026fa9435fd64147386df39780cbc271fa09c55dbdc", "");
    }
}
