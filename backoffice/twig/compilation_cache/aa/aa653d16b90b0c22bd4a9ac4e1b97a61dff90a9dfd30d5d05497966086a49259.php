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

/* __string_template__a54ab2dd6f595309d91f0983838e8c1085904f5b3fcda2343a423016914c762f */
class __TwigTemplate_06df8cb42533fa69adefa9e4cd6405b57d7bad4d8ce66916e8462f9a3f9e2109 extends Template
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
        echo "Welcome to ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["site"] ?? null), "site_name", [], "any", false, false, false, 1), "html", null, true);
        echo ". Confirm your account!";
    }

    public function getTemplateName()
    {
        return "__string_template__a54ab2dd6f595309d91f0983838e8c1085904f5b3fcda2343a423016914c762f";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("Welcome to {{site.site_name}}. Confirm your account!", "__string_template__a54ab2dd6f595309d91f0983838e8c1085904f5b3fcda2343a423016914c762f", "");
    }
}
