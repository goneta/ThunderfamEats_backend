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

/* __string_template__02c48feb19ee9f0bd198ab7e726cb8a22776e2c7dc870fce696abf5535b0f87e */
class __TwigTemplate_4ad1382fba6674594ac3bb9dee0bcb381d92aa43ba3fcca4bd2d4d207f2d2e91 extends Template
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
        echo "You have new merchant signup";
    }

    public function getTemplateName()
    {
        return "__string_template__02c48feb19ee9f0bd198ab7e726cb8a22776e2c7dc870fce696abf5535b0f87e";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("You have new merchant signup", "__string_template__02c48feb19ee9f0bd198ab7e726cb8a22776e2c7dc870fce696abf5535b0f87e", "");
    }
}
