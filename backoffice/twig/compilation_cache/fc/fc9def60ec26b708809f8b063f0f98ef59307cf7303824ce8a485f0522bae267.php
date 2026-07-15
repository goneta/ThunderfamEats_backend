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

/* __string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb */
class __TwigTemplate_43aace34372fef152ab9b6d1f1c537c87a8fb8a07852d04a06fd1d9180aa7162 extends Template
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
        $this->loadTemplate("header.html", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", 1)->display($context);
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
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Your order #";
        // line 10
        echo twig_escape_filter($this->env, ($context["order_id"] ?? null), "html", null, true);
        echo " has been rejected</h2>
    <p style=\"padding:10px;\">unfortunately merchant cannot fulfill your order, merchant says <b>";
        // line 11
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["order_info"] ?? null), "rejetion_reason", [], "any", false, false, false, 11), "html", null, true);
        echo "</b></p>
    
   </td>
 </tr>
 
 <tr>
  <td style=\"background:#fef9ef;\">
  
     ";
        // line 19
        $this->loadTemplate("summary.html", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", 19)->display($context);
        // line 20
        echo "   
  </td>
 </tr>
 
 <tr>
   <td style=\"background:#ffffff;\">
     ";
        // line 26
        $this->loadTemplate("items.html", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", 26)->display($context);
        // line 27
        echo "   </td>
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
        // line 40
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 40), "html", null, true);
        echo "</p>
         <p>";
        // line 41
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 41), "html", null, true);
        echo "</p>
         <p>";
        // line 42
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 42), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t      ";
        // line 45
        $this->loadTemplate("social_link.html", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", 45)->display($context);
        // line 46
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
        // line 63
        $this->loadTemplate("footer.html", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", 63)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  129 => 63,  110 => 46,  108 => 45,  102 => 42,  98 => 41,  94 => 40,  79 => 27,  77 => 26,  69 => 20,  67 => 19,  56 => 11,  52 => 10,  44 => 5,  39 => 2,  37 => 1,);
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
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Your order #{{order_id}} has been rejected</h2>
    <p style=\"padding:10px;\">unfortunately merchant cannot fulfill your order, merchant says <b>{{order_info.rejetion_reason}}</b></p>
    
   </td>
 </tr>
 
 <tr>
  <td style=\"background:#fef9ef;\">
  
     {% include 'summary.html' %}
   
  </td>
 </tr>
 
 <tr>
   <td style=\"background:#ffffff;\">
     {% include 'items.html' %}
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
", "__string_template__c1106a391a39d6f8d87851ddb6d10e09ac96b86e0bd4d86b35d9e790e14ae9fb", "");
    }
}
