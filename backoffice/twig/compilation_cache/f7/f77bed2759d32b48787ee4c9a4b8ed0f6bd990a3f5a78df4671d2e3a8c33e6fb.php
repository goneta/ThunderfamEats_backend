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

/* __string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b */
class __TwigTemplate_35b0965637fd212afa8893f4c3bf9a8ff9a5bc11e9eab91231f6619bbd7cde4f extends Template
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
        $this->loadTemplate("header.html", "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b", 1)->display($context);
        // line 2
        echo "
<table style=\"width:100%;\">
 <tbody><tr>
  <td style=\"background:#fef9ef;padding:20px 30px;\">
    <img style=\"max-width:20%;max-height:50px;\" src=\"";
        // line 6
        echo twig_escape_filter($this->env, ($context["logo"] ?? null), "html", null, true);
        echo "\">
  </td>
 </tr>
 <tr>
   <td style=\"padding:30px;padding-bottom:10px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Invoice #";
        // line 11
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["additional_data"] ?? null), "invoice_number", [], "any", false, false, false, 11), "html", null, true);
        echo "</h2>    
   </td>   
 </tr>
 <tr>
   <td style=\"padding-bottom:10px;background:#ffffff;\" valign=\"middle\">
     <table width=\"80%\" align=\"center\">
      <tbody><tr> 
       <td>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ullamcorper sapien ullamcorper nibh aliquam, non rutrum orci vulputate. Donec congue ac tortor eu dignissim. Cras a libero lobortis tellus elementum consequat eget vitae turpis. Mauris non lorem odio. Integer in lacus bibendum, accumsan risus nec, pretium felis. Aliquam auctor nec eros a mattis. Praesent eu ligula vitae ex rhoncus aliquam. Pellentesque ut mattis lectus. Maecenas ultrices a lorem et interdum. Mauris lacinia nec libero id tincidunt. Nunc accumsan quis enim vitae pellentesque.</p>        
       </td>
      </tr>
     </tbody></table>
   </td>   
 </tr>
 
 <tr>
  <td style=\"background:#fef9ef;\">
  
     ";
        // line 29
        $this->loadTemplate("summary.html", "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b", 29)->display($context);
        // line 30
        echo "  </td>
 </tr>
 
 <tr>
  <td style=\"padding:30px;\" align=\"center\">
     <a href=\"";
        // line 35
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["additional_data"] ?? null), "payment_link", [], "any", false, false, false, 35), "html", null, true);
        echo "\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Pay Now
     </a>
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
        // line 52
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "address", [], "any", false, false, false, 52), "html", null, true);
        echo "</p>
         <p>";
        // line 53
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "contact", [], "any", false, false, false, 53), "html", null, true);
        echo "</p>
         <p>";
        // line 54
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "email", [], "any", false, false, false, 54), "html", null, true);
        echo "</p>
\t    </td><td colspan=\"7\" style=\"padding:0 3px;\" valign=\"top\">
\t    
\t    ";
        // line 57
        $this->loadTemplate("social_link.html", "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b", 57)->display($context);
        // line 58
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
        // line 77
        $this->loadTemplate("footer.html", "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b", 77)->display($context);
    }

    public function getTemplateName()
    {
        return "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  140 => 77,  119 => 58,  117 => 57,  111 => 54,  107 => 53,  103 => 52,  83 => 35,  76 => 30,  74 => 29,  53 => 11,  45 => 6,  39 => 2,  37 => 1,);
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
   <td style=\"padding:30px;padding-bottom:10px;background:#ffffff;\" valign=\"middle\" align=\"center\">
    <h2 style=\"margin:0;\">Invoice #{{additional_data.invoice_number}}</h2>    
   </td>   
 </tr>
 <tr>
   <td style=\"padding-bottom:10px;background:#ffffff;\" valign=\"middle\">
     <table width=\"80%\" align=\"center\">
      <tbody><tr> 
       <td>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ullamcorper sapien ullamcorper nibh aliquam, non rutrum orci vulputate. Donec congue ac tortor eu dignissim. Cras a libero lobortis tellus elementum consequat eget vitae turpis. Mauris non lorem odio. Integer in lacus bibendum, accumsan risus nec, pretium felis. Aliquam auctor nec eros a mattis. Praesent eu ligula vitae ex rhoncus aliquam. Pellentesque ut mattis lectus. Maecenas ultrices a lorem et interdum. Mauris lacinia nec libero id tincidunt. Nunc accumsan quis enim vitae pellentesque.</p>        
       </td>
      </tr>
     </tbody></table>
   </td>   
 </tr>
 
 <tr>
  <td style=\"background:#fef9ef;\">
  
     {% include 'summary.html' %}
  </td>
 </tr>
 
 <tr>
  <td style=\"padding:30px;\" align=\"center\">
     <a href=\"{{additional_data.payment_link}}\" target=\"_blank\" style=\"display:block;margin:auto;max-width:200px;padding:10px;background:#3ecf8e;color:#fff;
     text-decoration:none;font-size:18px;font-weight:bold;\">
     Pay Now
     </a>
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


{% include 'footer.html' %}
", "__string_template__bcd0d7bd409e34a65eafe15c5e7e47daf673b9c7719b86bb608b3836e818151b", "");
    }
}
