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

/* __string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6 */
class __TwigTemplate_5e8e872ab7ba6d199380388b90fd29b5b1253e6adb13888c1bdd6cc3f1d05e41 extends Template
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
        $this->loadTemplate("header.html", "__string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6", 1)->display($context);
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
\t <p style=\"margin-bottom:10px;\">Welcome</p>
\t <p>Before you get full access to all features of your restaurant in ";
        // line 18
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "site_name", [], "any", false, false, false, 18), "html", null, true);
        echo ", please confirm your email address</p>
\t
\t<div style=\"margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;\">
\t <a href=\"";
        // line 21
        echo twig_escape_filter($this->env, ($context["confirm_link"] ?? null), "html", null, true);
        echo "\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Confirm email
     </a>
\t</div>
\t 
\t<p style=\"text-align:center;\">or click this link:</p>
\t<p style=\"text-align:center;\"><a href=\"";
        // line 28
        echo twig_escape_filter($this->env, ($context["confirm_link"] ?? null), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, ($context["confirm_link"] ?? null), "html", null, true);
        echo "</a></p>
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
        // line 50
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 50), "html", null, true);
        echo "</p>
         <p>";
        // line 51
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 51), "html", null, true);
        echo "</p>
         <p>";
        // line 52
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 52), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t      ";
        // line 55
        $this->loadTemplate("social_link.html", "__string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6", 55)->display($context);
        // line 56
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
        // line 73
        $this->loadTemplate("footer.html", "__string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6", 73)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  141 => 73,  122 => 56,  120 => 55,  114 => 52,  110 => 51,  106 => 50,  79 => 28,  69 => 21,  63 => 18,  57 => 15,  44 => 5,  39 => 2,  37 => 1,);
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
\t <p style=\"margin-bottom:10px;\">Welcome</p>
\t <p>Before you get full access to all features of your restaurant in {{site.site_name}}, please confirm your email address</p>
\t
\t<div style=\"margin:auto;text-align:center;padding-top:10px; padding-bottom:10px;\">
\t <a href=\"{{confirm_link}}\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Confirm email
     </a>
\t</div>
\t 
\t<p style=\"text-align:center;\">or click this link:</p>
\t<p style=\"text-align:center;\"><a href=\"{{confirm_link}}\">{{confirm_link}}</a></p>
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
", "__string_template__7c9d600b66e751f8b10c90b2e38be40c2eaa6cce1b5337b8f33a9ac4478b2cf6", "");
    }
}
