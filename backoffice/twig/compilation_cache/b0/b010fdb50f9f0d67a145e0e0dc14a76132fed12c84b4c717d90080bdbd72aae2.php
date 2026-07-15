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

/* __string_template__ffda525c3f0aaf3b7ddc241daeefa3bed07f3fc7deafa7e75a5f445418a3bd7c */
class __TwigTemplate_89624c6e6fe3aa7550eea24e5c10fddf27e100dd4076e563e75dc9a51c541339 extends Template
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
        echo "Test runactions";
    }

    public function getTemplateName()
    {
        return "__string_template__ffda525c3f0aaf3b7ddc241daeefa3bed07f3fc7deafa7e75a5f445418a3bd7c";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("Test runactions", "__string_template__ffda525c3f0aaf3b7ddc241daeefa3bed07f3fc7deafa7e75a5f445418a3bd7c", "");
    }
}
