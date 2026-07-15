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

/* __string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242 */
class __TwigTemplate_4ccad3e91b6b93be4217e960c42c6ba0d4b350ffcb182f614ee9017e1625edba extends Template
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
        $this->loadTemplate("header.html", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", 1)->display($context);
        // line 2
        echo "


<table style=\"width:100%;\">
 <tbody><tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    <img style=\"max-width:15%;max-height:50px;\" src=\"";
        // line 8
        echo twig_escape_filter($this->env, ($context["logo"] ?? null), "html", null, true);
        echo "\">
  </td>
 </tr>
 <tr>
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Order Accepted<br></h2>
    <p>Your order is confirmed and is now being prepared by the store. We'll let you know once our rider is on his way to you.</p><p>Conveniently track your order by clicking track order.<br></p>
    <a href=\"";
        // line 15
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["order"] ?? null), "order_info", [], "any", false, false, false, 15), "tracking_link", [], "any", false, false, false, 15), "html", null, true);
        echo "\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Track Order
     </a>
    
   </td>
 </tr>
 
 <tr>
  <td style=\"background:#fef9ef;\">
      ";
        // line 25
        $this->loadTemplate("summary.html", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", 25)->display($context);
        // line 26
        echo "  </td>
 </tr>
 
 <tr>
   <td style=\"background:#ffffff;\">
     ";
        // line 31
        $this->loadTemplate("items.html", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", 31)->display($context);
        // line 32
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
        // line 45
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 45), "html", null, true);
        echo "</p>
         <p>";
        // line 46
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 46), "html", null, true);
        echo "</p>
         <p>";
        // line 47
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 47), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t    ";
        // line 50
        $this->loadTemplate("social_link.html", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", 50)->display($context);
        // line 51
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
        // line 69
        $this->loadTemplate("footer.html", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", 69)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  132 => 69,  112 => 51,  110 => 50,  104 => 47,  100 => 46,  96 => 45,  81 => 32,  79 => 31,  72 => 26,  70 => 25,  57 => 15,  47 => 8,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% include 'header.html' %}



<table style=\"width:100%;\">
 <tbody><tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    <img style=\"max-width:15%;max-height:50px;\" src=\"{{logo}}\">
  </td>
 </tr>
 <tr>
   <td style=\"padding:30px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Order Accepted<br></h2>
    <p>Your order is confirmed and is now being prepared by the store. We'll let you know once our rider is on his way to you.</p><p>Conveniently track your order by clicking track order.<br></p>
    <a href=\"{{order.order_info.tracking_link}}\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Track Order
     </a>
    
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
\t    {% include 'social_link.html' %}
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

{% include 'footer.html' %}", "__string_template__c0f4368777e25e219b51d8987768f2c9b98739be17c18132920fdfcecb2fa242", "");
    }
}
