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

/* __string_template__46bb669f4bac5d0aa75ce5377131aad868fc124959dbbb60df3c715c97921fed */
class __TwigTemplate_3b144961c00e763d71215457d78255afbc17568cb635286222047880f5ae56ee extends Template
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
        echo "Order Summary";
    }

    public function getTemplateName()
    {
        return "__string_template__46bb669f4bac5d0aa75ce5377131aad868fc124959dbbb60df3c715c97921fed";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("Order Summary", "__string_template__46bb669f4bac5d0aa75ce5377131aad868fc124959dbbb60df3c715c97921fed", "");
    }
}
