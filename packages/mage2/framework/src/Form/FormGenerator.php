<?php

namespace Mage2\Framework\Form;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Session\SessionInterface;
use Illuminate\Filesystem\Filesystem;

class FormGenerator {

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The URL generator instance.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * The View factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * The CSRF token used by the form builder.
     *
     * @var string
     */
    protected $csrfToken;

    /**
     * The session store implementation.
     *
     * @var \Illuminate\Session\SessionInterface
     */
    protected $session;

    /**
     * The current model instance for the form.
     *
     * @var mixed
     */
    protected $model;

    /**
     * Create a new form generator instance.
     *
     * @param  \Illuminate\Filesystem\                      $fileSystem
     * @param  \Illuminate\Contracts\Routing\UrlGenerator   $url
     * @param  \Illuminate\Contracts\View\Factory           $view
     * @param  string                                       $csrfToken
     */
    public function __construct(Filesystem $fileSystem, UrlGenerator $url, Factory $view, $csrfToken) {
        $this->files = $fileSystem;
        $this->url = $url;
        $this->view = $view;
        $this->csrfToken = $csrfToken;
    }

    /**
     * bind the form with model
     * 
     * @todo add attribute feature and etc
     *
     * @param  Object  $model
     * @param  Array  $dummyReplacement
     * @return $stub
     */
    public function bind($model, $dummyReplacement = []) {
        $this->model = $model;
        $stub = $this->open($dummyReplacement);

        return $stub;
    }

    /**
     * get the form open stub template
     * 
     * @todo add attribute feature and etc
     *
     * @param  Array  $dummyReplacement
     * @return $stub
     */
    public function open($dummyReplacement = []) {
        $stub = $this->files->get($this->getStub('form-open'));

        foreach ($dummyReplacement as $dummyText => $replacement) {
            $this->replaceStubText($stub, strtoupper("DUMMY" . $dummyText), $replacement);
        }

        $csrfStub = $this->files->get($this->getStub('_csrf'));
        $this->replaceStubText($csrfStub, "DUMMYCSRF", $this->csrfToken);
        $stub = $stub . $csrfStub;

        return $stub;
    }

    /**
     * get the form closestub template
     * @return $stub
     */
    public function close() {
        $stub = $this->files->get($this->getStub('form-close'));
        return $stub;
    }

    /**
     * get the text field using stub template 
     * 
     * @todo add attribute feature and etc
     *
     * @param  string  $fieldName
     * @param  string  $label
     * @param  array  $attributes
     * @return $stub
     */
    public function text($fieldName, $label = "" , $attributes = []) {
        $stub = $this->files->get($this->getStub('text'));
        $attributeText = $this->getAttributeText($attributes);
        $this->replaceStubText($stub, "DUMMYFIELDNAME", $fieldName);
        $this->replaceStubText($stub, "DUMMYLABEL", $label);
        
        $this->replaceStubText($stub, "DUMMYATTRIBUTES", $attributeText);

        $this->setValue($stub, $fieldName);

        return $stub;
    }

    /**
     * get the text field using stub template 
     * 
     * @todo add attribute feature and etc
     *
     * @param  string  $buttonText
     * @return $stub
     */
    public function submit($buttonText = "Save") {
        $stub = $this->files->get($this->getStub('submit'));

        $this->replaceStubText($stub, "DUMMYBUTTONTEXT", $buttonText);
        return $stub;
    }
    
     /**
     * get the attribuet text from given array
     * 
     * @todo add attribute feature and etc
     *
     * @param  string  $buttonText
     * @return $stub
     */
    public function getAttributeText($attributes = []) {
        $attributeText = "";
        foreach($attributes as $attKey => $attVal) {
            $attributeText .= $attKey . "=" . $attVal;
        }
        
        return $attributeText;
    }

    /**
     * Replace the dummy stub textfor the given stub.
     *
     * @param  string  $stub
     * @param  string  $fieldName
     * @return $this
     */
    protected function setValue(&$stub, $fieldName) {

        $value = (isset($this->model->$fieldName)) ? $this->model->$fieldName : "";
        $this->replaceStubText($stub, "DUMMYVALUE", $value);
        return $this;
    }

    /**
     * Replace the dummy stub textfor the given stub.
     *
     * @param  string  $stub
     * @param  string  $fieldName
     * @return $this
     */
    protected function replaceStubText(&$stub, $dummyText, $fieldName) {
        $stub = str_replace($dummyText, $fieldName, $stub);
        return $this;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($name) {
        return __DIR__ . "/stubs/{$name}.stub";
    }

    /**
     * Set the session store implementation.
     *
     * @param  \Illuminate\Session\SessionInterface $session
     *
     * @return $this
     */
    public function setSessionStore(SessionInterface $session) {
        $this->session = $session;
        return $this;
    }

}