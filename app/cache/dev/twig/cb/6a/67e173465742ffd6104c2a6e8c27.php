<?php

/* SymfonyWebConfiguratorBundle::form.html.twig */
class __TwigTemplate_cb6a67e173465742ffd6104c2a6e8c27 extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'field_row' => array($this, 'block_field_row'),
            'label' => array($this, 'block_label'),
            'errors' => array($this, 'block_errors'),
            'text_field' => array($this, 'block_text_field'),
            'password_field' => array($this, 'block_password_field'),
        );
    }

    public function getParent(array $context)
    {
        if (null === $this->parent) {
            $this->parent = $this->env->loadTemplate("TwigBundle::form.html.twig");
        }

        return $this->parent;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_field_row($context, array $blocks = array())
    {
        // line 4
        echo "    <div class=\"symfony-form-row\">
        ";
        // line 5
        echo $this->env->getExtension('form')->renderLabel($this->getContext($context, 'child'));
        echo "
        <div class=\"symfony-form-field\">
            ";
        // line 7
        echo $this->env->getExtension('form')->renderField($this->getContext($context, 'child'));
        echo "
            <div class=\"symfony-form-errors\">
                ";
        // line 9
        echo $this->env->getExtension('form')->renderErrors($this->getContext($context, 'child'));
        echo "
            </div>
        </div>
    </div>
";
    }

    // line 15
    public function block_label($context, array $blocks = array())
    {
        // line 16
        echo "    <label for=\"";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'field'), "id", array(), "any", false), "html");
        echo "\">
        ";
        // line 17
        echo $this->env->getExtension('translator')->getTranslator()->trans($this->getContext($context, 'label'), array(), "messages");
        // line 18
        echo "        ";
        if ($this->getAttribute($this->getContext($context, 'field'), "required", array(), "any", false)) {
            // line 19
            echo "            <span class=\"symfony-form-required\" title=\"This field is required\">*</span>
        ";
        }
        // line 21
        echo "    </label>
";
    }

    // line 24
    public function block_errors($context, array $blocks = array())
    {
        // line 25
        echo "    ";
        if ($this->getAttribute($this->getContext($context, 'field'), "hasErrors", array(), "any", false)) {
            // line 26
            echo "        <ul>
        ";
            // line 27
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getContext($context, 'field'), "errors", array(), "any", false));
            foreach ($context['_seq'] as $context['_key'] => $context['error']) {
                // line 28
                echo "            <li>";
                echo $this->env->getExtension('translator')->getTranslator()->trans($this->getAttribute($this->getContext($context, 'error'), "messageTemplate", array(), "any", false), array_merge(array(), $this->getAttribute($this->getContext($context, 'error'), "messageParameters", array(), "any", false)), "validators");
                echo "</li>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 30
            echo "        </ul>
    ";
        }
    }

    // line 34
    public function block_text_field($context, array $blocks = array())
    {
        // line 35
        echo "    ";
        if (($this->getAttribute($this->getContext($context, 'attr'), "type", array(), "any", true) && ($this->getAttribute($this->getContext($context, 'attr'), "type", array(), "any", false) != "text"))) {
            // line 36
            echo "        <input ";
            echo twig_escape_filter($this->env, $this->renderBlock("field_attributes", $context, $blocks), "html");
            echo " value=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'field'), "displayedData", array(), "any", false), "html");
            echo "\" />
    ";
        } else {
            // line 38
            echo "        ";
            $context['attr'] = twig_array_merge($this->getContext($context, 'attr'), array("maxlength" => (($this->getAttribute($this->getContext($context, 'attr'), "maxlength", array(), "any", true)) ? (twig_default_filter($this->getAttribute($this->getContext($context, 'attr'), "maxlength", array(), "any", true), $this->getAttribute($this->getContext($context, 'field'), "maxlength", array(), "any", false))) : ($this->getAttribute($this->getContext($context, 'field'), "maxlength", array(), "any", false)))));
            // line 39
            echo "        <input type=\"text\" ";
            echo twig_escape_filter($this->env, $this->renderBlock("field_attributes", $context, $blocks), "html");
            echo " value=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'field'), "displayedData", array(), "any", false), "html");
            echo "\" />
    ";
        }
    }

    // line 43
    public function block_password_field($context, array $blocks = array())
    {
        // line 44
        echo "    ";
        $context['attr'] = twig_array_merge($this->getContext($context, 'attr'), array("maxlength" => (($this->getAttribute($this->getContext($context, 'attr'), "maxlength", array(), "any", true)) ? (twig_default_filter($this->getAttribute($this->getContext($context, 'attr'), "maxlength", array(), "any", true), $this->getAttribute($this->getContext($context, 'field'), "maxlength", array(), "any", false))) : ($this->getAttribute($this->getContext($context, 'field'), "maxlength", array(), "any", false)))));
        // line 45
        echo "    <input type=\"password\" ";
        echo twig_escape_filter($this->env, $this->renderBlock("field_attributes", $context, $blocks), "html");
        echo " class=\"medium_txt\" value=\"";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'field'), "displayedData", array(), "any", false), "html");
        echo "\" />
";
    }

    public function getTemplateName()
    {
        return "SymfonyWebConfiguratorBundle::form.html.twig";
    }
}
