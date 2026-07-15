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

/* __string_template__231c1968aa9681c38c367a08521f3d40abe18e1f7ce78c5c9e814924c42a4e64 */
class __TwigTemplate_eb178c5cc84434454b5dc5977afff16edf3d5a0cbe02947386db9d244af3a0a2 extends Template
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
        echo twig_escape_filter($this->env, ($context["order_id"] ?? null), "html", null, true);
        echo " has been rejected";
    }

    public function getTemplateName()
    {
        return "__string_template__231c1968aa9681c38c367a08521f3d40abe18e1f7ce78c5c9e814924c42a4e64";
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
        return new Source("Your order #{{order_id}} has been rejected", "__string_template__231c1968aa9681c38c367a08521f3d40abe18e1f7ce78c5c9e814924c42a4e64", "");
    }
}
