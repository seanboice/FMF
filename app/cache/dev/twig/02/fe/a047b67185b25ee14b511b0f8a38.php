<?php

/* SymfonyWebConfiguratorBundle:Step:csrf.html.twig */
class __TwigTemplate_02fea047b67185b25ee14b511b0f8a38 extends Twig_Template
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
        echo "Symfony - Configure CSRF";
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
    <h1>CSRF Protection</h1>
    <p>Configure CSRF protection for your website :</p>

    ";
        // line 12
        echo $this->env->getExtension('form')->renderErrors($this->getContext($context, 'form'));
        echo "
    <form action=\"";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_configurator_step", array("index" => $this->getContext($context, 'index'))), "html");
        echo " \" method=\"POST\">
        ";
        // line 14
        echo $this->env->getExtension('form')->renderHidden($this->getContext($context, 'form'));
        echo "

        <div class=\"symfony-form-row\">
            ";
        // line 17
        echo $this->env->getExtension('form')->renderLabel($this->getAttribute($this->getContext($context, 'form'), "csrf_secret", array(), "any", false));
        echo "
            <div class=\"symfony-form-field\">
                ";
        // line 19
        echo $this->env->getExtension('form')->renderField($this->getAttribute($this->getContext($context, 'form'), "csrf_secret", array(), "any", false));
        echo "
                <a class=\"symfony-button-grey\" href=\"#\" onclick=\"generateCsrf(); return false;\">Generate</a>
                <div class=\"symfony-form-errors\">
                    ";
        // line 22
        echo $this->env->getExtension('form')->renderErrors($this->getAttribute($this->getContext($context, 'form'), "csrf_secret", array(), "any", false));
        echo "
                </div>
            </div>
        </div>

        <div class=\"symfony-form-footer\">
            <p><input type=\"submit\" value=\"Next Step\" class=\"symfony-button-grey\" /></p>
            <p>* mandatory fields</p>
        </div>

    </form>

    <script type=\"text/javascript\">
        function generateCsrf()
        {
            var result = '';
            for (i=0; i < 32; i++) {
                result += Math.round(Math.random()*16).toString(16);
            }
            document.getElementById('config_csrf_secret').value = result;
        }
    </script>

";
    }

    public function getTemplateName()
    {
        return "SymfonyWebConfiguratorBundle:Step:csrf.html.twig";
    }
}
