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

/* __string_template__7b540d68084c55c2c817bc40a637e44ad9af458e793858f480d069aca36fa2c1 */
class __TwigTemplate_9a9d97d8e0aa81ffe61f9514f34e85cb1177402408a962cca2ae070294136f0b extends Template
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
        echo "OTP!";
    }

    public function getTemplateName()
    {
        return "__string_template__7b540d68084c55c2c817bc40a637e44ad9af458e793858f480d069aca36fa2c1";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("OTP!", "__string_template__7b540d68084c55c2c817bc40a637e44ad9af458e793858f480d069aca36fa2c1", "");
    }
}
