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

/* __string_template__761e4594636055cb1bda0da43b8dc95f23cd2c7030297d3b6d10c23fc19972a8 */
class __TwigTemplate_ed2a481380a0b85d602cae45edeec8d689294c506bd22d10036e7bd5f563e5cd extends Template
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
        echo "Your order #";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["order_info"] ?? null), "order_id", [], "any", false, false, false, 1), "html", null, true);
        echo " is accepted by ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["merchant"] ?? null), "restaurant_name", [], "any", false, false, false, 1), "html", null, true);
    }

    public function getTemplateName()
    {
        return "__string_template__761e4594636055cb1bda0da43b8dc95f23cd2c7030297d3b6d10c23fc19972a8";
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
        return new Source("Your order #{{order_info.order_id}} is accepted by {{merchant.restaurant_name}}", "__string_template__761e4594636055cb1bda0da43b8dc95f23cd2c7030297d3b6d10c23fc19972a8", "");
    }
}
