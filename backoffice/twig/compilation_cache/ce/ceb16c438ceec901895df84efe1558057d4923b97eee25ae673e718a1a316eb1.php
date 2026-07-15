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

/* __string_template__fd6126587791654af4041b9688e1bb9841e0b8e941295718520b60e100b2e061 */
class __TwigTemplate_8b937b361f24dcc1f6d27954fdaa87ef44e4ae03d96094d367fc73787989bc3c extends Template
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
        echo "<p>this is a test runactions<br></p>";
    }

    public function getTemplateName()
    {
        return "__string_template__fd6126587791654af4041b9688e1bb9841e0b8e941295718520b60e100b2e061";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<p>this is a test runactions<br></p>", "__string_template__fd6126587791654af4041b9688e1bb9841e0b8e941295718520b60e100b2e061", "");
    }
}
