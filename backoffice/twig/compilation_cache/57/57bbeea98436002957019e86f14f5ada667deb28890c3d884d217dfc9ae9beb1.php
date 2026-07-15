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

/* __string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7 */
class __TwigTemplate_06a513c3772ca521536fb47038df4c20c77381f89330f6b0b75f3966c99c585b extends Template
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
        $this->loadTemplate("header.html", "__string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7", 1)->display($context);
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
    
    <p style=\"margin-bottom:15px;\">Hi <br></p>
\t
\t<p style=\"margin-bottom: 15px;\">You have new merchant signup.</p>\t
\t
\t<h5>Customer Details</h5>
\t<table width=\"60%\">
\t <tbody><tr>
\t  <td width=\"25%\">Restaurant name<br></td>
\t  <td>";
        // line 19
        echo twig_escape_filter($this->env, ($context["restaurant_name"] ?? null), "html", null, true);
        echo "</td>
\t </tr>
\t <tr>
\t  <td>Address<br></td>
\t  <td>";
        // line 23
        echo twig_escape_filter($this->env, ($context["address"] ?? null), "html", null, true);
        echo "</td>
\t </tr>\t
\t  <tr>
\t  <td>Membership Program<br></td>
\t  <td>";
        // line 27
        echo twig_escape_filter($this->env, ($context["plan_title"] ?? null), "html", null, true);
        echo "</td>
\t </tr>\t
\t  <tr>
\t  <td>Phone number</td>
\t  <td>";
        // line 31
        echo twig_escape_filter($this->env, ($context["contact_phone"] ?? null), "html", null, true);
        echo "</td>
\t </tr><tr><td>Email address<br></td><td>";
        // line 32
        echo twig_escape_filter($this->env, ($context["contact_email"] ?? null), "html", null, true);
        echo "<br></td></tr>\t 
\t</tbody></table>
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
        // line 51
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 51), "html", null, true);
        echo "</p>
         <p>";
        // line 52
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 52), "html", null, true);
        echo "</p>
         <p>";
        // line 53
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 53), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t      ";
        // line 56
        $this->loadTemplate("social_link.html", "__string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7", 56)->display($context);
        // line 57
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
        // line 74
        $this->loadTemplate("footer.html", "__string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7", 74)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  143 => 74,  124 => 57,  122 => 56,  116 => 53,  112 => 52,  108 => 51,  86 => 32,  82 => 31,  75 => 27,  68 => 23,  61 => 19,  44 => 5,  39 => 2,  37 => 1,);
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
    
    <p style=\"margin-bottom:15px;\">Hi <br></p>
\t
\t<p style=\"margin-bottom: 15px;\">You have new merchant signup.</p>\t
\t
\t<h5>Customer Details</h5>
\t<table width=\"60%\">
\t <tbody><tr>
\t  <td width=\"25%\">Restaurant name<br></td>
\t  <td>{{restaurant_name}}</td>
\t </tr>
\t <tr>
\t  <td>Address<br></td>
\t  <td>{{address}}</td>
\t </tr>\t
\t  <tr>
\t  <td>Membership Program<br></td>
\t  <td>{{plan_title}}</td>
\t </tr>\t
\t  <tr>
\t  <td>Phone number</td>
\t  <td>{{contact_phone}}</td>
\t </tr><tr><td>Email address<br></td><td>{{contact_email}}<br></td></tr>\t 
\t</tbody></table>
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
", "__string_template__312b0dbc798af98f5e57ffd390edab9176574b74cae3c623f212f994d08884a7", "");
    }
}
