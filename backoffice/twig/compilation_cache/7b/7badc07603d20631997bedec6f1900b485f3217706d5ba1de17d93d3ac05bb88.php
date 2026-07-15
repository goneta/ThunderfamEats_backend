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

/* __string_template__dde8b1504a213ab56c67d932a51bdc3fc18fecc958b052862082e09031b9b4d8 */
class __TwigTemplate_30f8811e7f9a9cca8758e58ef90a75f6ebd32bd9f9a7b98a58f594bc968b12d9 extends Template
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
        echo ", has a balance of ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["additional_data"] ?? null), "balance", [], "any", false, false, false, 1), "html", null, true);
        echo ".
pay here ";
        // line 2
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["additional_data"] ?? null), "payment_link", [], "any", false, false, false, 2), "html", null, true);
    }

    public function getTemplateName()
    {
        return "__string_template__dde8b1504a213ab56c67d932a51bdc3fc18fecc958b052862082e09031b9b4d8";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("Your order #{{order_info.order_id}}, has a balance of {{additional_data.balance}}.
pay here {{additional_data.payment_link}}", "__string_template__dde8b1504a213ab56c67d932a51bdc3fc18fecc958b052862082e09031b9b4d8", "");
    }
}
