<?php

/* SymfonyWebConfiguratorBundle:Step:doctrine.html.twig */
class __TwigTemplate_388ea76d5b40763fa29c3caad7e1b184 extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    public function getParent(array $context)
    {
        if (null === $this->parent) {
            $this->parent = $this->env->loadTemplate("SymfonyWebConfiguratorBundle::layout.html.twig");
        }

        return $this->parent;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        echo "Symfony - Configure database";
    }

    // line 5
    public function block_content($context, array $blocks = array())
    {
        // line 6
        echo "    ";
        echo $this->env->getExtension('form')->setTheme($this->getContext($context, 'form'), array("SymfonyWebConfiguratorBundle::form.html.twig", ));        // line 7
        echo "    ";
        $template = "SymfonyWebConfiguratorBundle::steps.html.twig";
        if ($template instanceof Twig_Template) {
            $template->display(array_merge($context, array("index" => $this->getContext($context, 'index'), "count" => $this->getContext($context, 'count'))));
        } else {
            echo $this->env->getExtension('templating')->getTemplating()->render($template, array_merge($context, array("index" => $this->getContext($context, 'index'), "count" => $this->getContext($context, 'count'))));
        }
        // line 8
        echo "
    <h1>Configure your Database</h1>
    <p>If your website needs a database connection, please configure it here.</p>

    ";
        // line 12
        echo $this->env->getExtension('form')->renderErrors($this->getContext($context, 'form'));
        echo "
    <form action=\"";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_configurator_step", array("index" => $this->getContext($context, 'index'))), "html");
        echo "\" method=\"POST\">
        ";
        // line 14
        echo $this->env->getExtension('form')->renderHidden($this->getContext($context, 'form'));
        echo "

        ";
        // line 16
        echo $this->env->getExtension('form')->renderRow($this->getAttribute($this->getContext($context, 'form'), "driver", array(), "any", false));
        echo "
        ";
        // line 17
        echo $this->env->getExtension('form')->renderRow($this->getAttribute($this->getContext($context, 'form'), "name", array(), "any", false));
        echo "
        ";
        // line 18
        echo $this->env->getExtension('form')->renderRow($this->getAttribute($this->getContext($context, 'form'), "host", array(), "any", false));
        echo "
        ";
        // line 19
        echo $this->env->getExtension('form')->renderRow($this->getAttribute($this->getContext($context, 'form'), "user", array(), "any", false));
        echo "
        <div class=\"symfony-form-row\">
            ";
        // line 21
        echo $this->env->getExtension('form')->renderLabel($this->getAttribute($this->getContext($context, 'form'), "password", array(), "any", false));
        echo "
            <div class=\"symfony-form-field\">
                ";
        // line 23
        echo $this->env->getExtension('form')->renderField($this->getAttribute($this->getAttribute($this->getContext($context, 'form'), "password", array(), "any", false), "Password", array(), "any", false));
        echo "
                <div class=\"symfony-form-errors\">
                    ";
        // line 25
        echo $this->env->getExtension('form')->renderErrors($this->getAttribute($this->getContext($context, 'form'), "password", array(), "any", false));
        echo "
                </div>
            </div>
        </div>
        ";
        // line 29
        echo $this->env->getExtension('form')->renderRow($this->getAttribute($this->getAttribute($this->getContext($context, 'form'), "password", array(), "any", false), "Again", array(), "any", false));
        echo "

        <div class=\"symfony-form-footer\">
            <p><input type=\"submit\" value=\"Next Step\" class=\"symfony-button-grey\" /></p>
            <p>* mandatory fields</p>
        </div>
    </form>
";
    }

    public function getTemplateName()
    {
        return "SymfonyWebConfiguratorBundle:Step:doctrine.html.twig";
    }
}
