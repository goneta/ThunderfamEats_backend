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

/* __string_template__b626bfa66e1105da9526bd8cf3f0d186d09b8e3a5d10237c0de2fe5df1086b47 */
class __TwigTemplate_14b85b15c6fc4ce6c2584350e034206ebc573c86b4e8e2c219a3e8299f48cd3a extends Template
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
        echo "Your registration is complete!";
    }

    public function getTemplateName()
    {
        return "__string_template__b626bfa66e1105da9526bd8cf3f0d186d09b8e3a5d10237c0de2fe5df1086b47";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("Your registration is complete!", "__string_template__b626bfa66e1105da9526bd8cf3f0d186d09b8e3a5d10237c0de2fe5df1086b47", "");
    }
}
