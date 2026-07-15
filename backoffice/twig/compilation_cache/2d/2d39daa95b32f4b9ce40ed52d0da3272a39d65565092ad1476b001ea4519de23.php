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

/* __string_template__f37ba9aecb1dd2f7d4fc19f71128a826b639a593de0918c377d8e1745a4361b9 */
class __TwigTemplate_ed34be6aac0adaefc89c52c25c46a7686a06f7a9d7c4cf49cdc9417a0011f854 extends Template
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
        echo "Invoice for your order #";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["order_info"] ?? null), "order_id", [], "any", false, false, false, 1), "html", null, true);
    }

    public function getTemplateName()
    {
        return "__string_template__f37ba9aecb1dd2f7d4fc19f71128a826b639a593de0918c377d8e1745a4361b9";
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
        return new Source("Invoice for your order #{{order_info.order_id}}", "__string_template__f37ba9aecb1dd2f7d4fc19f71128a826b639a593de0918c377d8e1745a4361b9", "");
    }
}
