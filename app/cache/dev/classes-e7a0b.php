<?php
namespace Symfony\Component\HttpFoundation
{
class Response
{
    public $headers;
    protected $content;
    protected $version;
    protected $statusCode;
    protected $statusText;
    protected $charset;
    static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
        $this->headers = new ResponseHeaderBag($headers);
    }
    public function __toString()
    {
        $content = '';
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html; charset='.(null === $this->charset ? 'UTF-8' : $this->charset));
        }
                $content .= sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\n";
                foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $content .= "$name: $value\n";
            }
        }
        $content .= "\n".$this->getContent();
        return $content;
    }
    public function __clone()
    {
        $this->headers = clone $this->headers;
    }
    public function sendHeaders()
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html; charset='.(null === $this->charset ? 'UTF-8' : $this->charset));
        }
                header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));
                foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value);
            }
        }
                foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
    }
    public function sendContent()
    {
        echo $this->content;
    }
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function setProtocolVersion($version)
    {
        $this->version = $version;
    }
    public function getProtocolVersion()
    {
        return $this->version;
    }
    public function setStatusCode($code, $text = null)
    {
        $this->statusCode = (int) $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }
        $this->statusText = false === $text ? '' : (null === $text ? self::$statusTexts[$this->statusCode] : $text);
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }
    public function getCharset()
    {
        return $this->charset;
    }
    public function isCacheable()
    {
        if (!in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false;
        }
        if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
            return false;
        }
        return $this->isValidateable() || $this->isFresh();
    }
    public function isFresh()
    {
        return $this->getTtl() > 0;
    }
    public function isValidateable()
    {
        return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
    }
    public function setPrivate()
    {
        $this->headers->removeCacheControlDirective('public');
        $this->headers->addCacheControlDirective('private');
    }
    public function setPublic()
    {
        $this->headers->addCacheControlDirective('public');
        $this->headers->removeCacheControlDirective('private');
    }
    public function mustRevalidate()
    {
        return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->has('must-proxy-revalidate');
    }
    public function getDate()
    {
        if (null === $date = $this->headers->getDate('Date')) {
            $date = new \DateTime(null, new \DateTimeZone('UTC'));
            $this->headers->set('Date', $date->format('D, d M Y H:i:s').' GMT');
        }
        return $date;
    }
    public function getAge()
    {
        if ($age = $this->headers->get('Age')) {
            return $age;
        }
        return max(time() - $this->getDate()->format('U'), 0);
    }
    public function expire()
    {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge());
        }
    }
    public function getExpires()
    {
        return $this->headers->getDate('Expires');
    }
    public function setExpires(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Expires');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Expires', $date->format('D, d M Y H:i:s').' GMT');
        }
    }
    public function getMaxAge()
    {
        if ($age = $this->headers->getCacheControlDirective('s-maxage')) {
            return $age;
        }
        if ($age = $this->headers->getCacheControlDirective('max-age')) {
            return $age;
        }
        if (null !== $this->getExpires()) {
            return $this->getExpires()->format('U') - $this->getDate()->format('U');
        }
        return null;
    }
    public function setMaxAge($value)
    {
        $this->headers->addCacheControlDirective('max-age', $value);
    }
    public function setSharedMaxAge($value)
    {
        $this->setPublic();
        $this->headers->addCacheControlDirective('s-maxage', $value);
    }
    public function getTtl()
    {
        if ($maxAge = $this->getMaxAge()) {
            return $maxAge - $this->getAge();
        }
        return null;
    }
    public function setTtl($seconds)
    {
        $this->setSharedMaxAge($this->getAge() + $seconds);
    }
    public function setClientTtl($seconds)
    {
        $this->setMaxAge($this->getAge() + $seconds);
    }
    public function getLastModified()
    {
        return $this->headers->getDate('Last-Modified');
    }
    public function setLastModified(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Last-Modified');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s').' GMT');
        }
    }
    public function getEtag()
    {
        return $this->headers->get('ETag');
    }
    public function setEtag($etag = null, $weak = false)
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"'.$etag.'"';
            }
            $this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag);
        }
    }
    public function setCache(array $options)
    {
        if ($diff = array_diff(array_keys($options), array('etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public'))) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_keys($diff))));
        }
        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }
        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }
        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }
        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }
        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }
        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }
    }
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);
                foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->remove($header);
        }
    }
    public function hasVary()
    {
        return (Boolean) $this->headers->get('Vary');
    }
    public function getVary()
    {
        if (!$vary = $this->headers->get('Vary')) {
            return array();
        }
        return is_array($vary) ? $vary : preg_split('/[\s,]+/', $vary);
    }
    public function setVary($headers, $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);
    }
    public function isNotModified(Request $request)
    {
        $lastModified = $request->headers->get('If-Modified-Since');
        $notModified = false;
        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->get('Last-Modified') == $lastModified);
        } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->get('Last-Modified');
        }
        if ($notModified) {
            $this->setNotModified();
        }
        return $notModified;
    }
        public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }
    public function isOk()
    {
        return 200 === $this->statusCode;
    }
    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }
    public function isRedirect()
    {
        return in_array($this->statusCode, array(301, 302, 303, 307));
    }
    public function isEmpty()
    {
        return in_array($this->statusCode, array(201, 204, 304));
    }
    public function isRedirected($location)
    {
        return $this->isRedirect() && $location == $this->headers->get('Location');
    }
}
}
namespace Symfony\Component\HttpFoundation
{
class ResponseHeaderBag extends HeaderBag
{
    protected $computedCacheControl = array();
    public function __construct(array $headers = array())
    {
        parent::__construct($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('cache-control', '');
        }
    }
    public function replace(array $headers = array())
    {
        parent::replace($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('cache-control', '');
        }
    }
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);
                if (in_array(strtr(strtolower($key), '_', '-'), array('cache-control', 'etag', 'last-modified', 'expires'))) {
            $computed = $this->computeCacheControlValue();
            $this->headers['cache-control'] = array($computed);
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }
    public function remove($key)
    {
        parent::remove($key);
        if ('cache-control' === strtr(strtolower($key), '_', '-')) {
            $this->computedCacheControl = array();
        }
    }
    public function hasCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl);
    }
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl) ? $this->computedCacheControl[$key] : null;
    }
    public function clearCookie($name, $path = null, $domain = null)
    {
        $this->setCookie(new Cookie($name, null, 1, $path, $domain));
    }
    protected function computeCacheControlValue()
    {
        if (!$this->cacheControl && !$this->has('ETag') && !$this->has('Last-Modified') && !$this->has('Expires')) {
            return 'no-cache';
        }
        if (!$this->cacheControl) {
                        return 'private, must-revalidate';
        }
        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }
                if (!isset($this->cacheControl['s-maxage'])) {
            return $header.', private';
        }
        return $header;
    }
}}
namespace Symfony\Component\EventDispatcher
{
class Event
{
    private $propagationStopped = false;
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }
}
}
namespace Symfony\Component\EventDispatcher
{
interface EventSubscriberInterface
{
    static function getSubscribedEvents();
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
class KernelEvent extends Event
{
    private $kernel;
    private $request;
    private $requestType;
    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->requestType = $requestType;
    }
    public function getKernel()
    {
        return $this->kernel;
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getRequestType()
    {
        return $this->requestType;
    }
}}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class FilterControllerEvent extends KernelEvent
{
    private $controller;
    public function __construct(HttpKernelInterface $kernel, $controller, Request $request, $requestType)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setController($controller);
    }
    public function getController()
    {
        return $this->controller;
    }
    public function setController($controller)
    {
                if (!is_callable($controller)) {
            throw new \LogicException(sprintf('The controller must be a callable (%s given).', $this->varToString($controller)));
        }
        $this->controller = $controller;
    }
    private function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }
            return sprintf("[array](%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }
        return str_replace("\n", '', var_export((string) $var, true));
    }
}}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class FilterResponseEvent extends KernelEvent
{
    private $response;
    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, Response $response)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setResponse($response);
    }
    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class GetResponseEvent extends KernelEvent
{
    private $response;
    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }
    public function hasResponse()
    {
        return null !== $this->response;
    }
}}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForControllerResultEvent extends GetResponseEvent
{
    private $controllerResult;
    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, $controllerResult)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->controllerResult = $controllerResult;
    }
    public function getControllerResult()
    {
        return $this->controllerResult;
    }
}}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForExceptionEvent extends GetResponseEvent
{
    private $exception;
    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, \Exception $e)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setException($e);
    }
    public function getException()
    {
        return $this->exception;
    }
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
}}
namespace Symfony\Component\HttpKernel
{
final class Events
{
    const onCoreRequest = 'onCoreRequest';
    const onCoreException = 'onCoreException';
    const onCoreView = 'onCoreView';
    const onCoreController = 'onCoreController';
    const onCoreResponse = 'onCoreResponse';
}}
namespace
{
class Twig_Markup
{
    protected $content;
    public function __construct($content)
    {
        $this->content = (string) $content;
    }
    public function __toString()
    {
        return $this->content;
    }
}
}
namespace
{
abstract class Twig_Template implements Twig_TemplateInterface
{
    static protected $cache = array();
    protected $env;
    protected $blocks;
    public function __construct(Twig_Environment $env)
    {
        $this->env = $env;
        $this->blocks = array();
    }
    public function getTemplateName()
    {
        return null;
    }
    public function getEnvironment()
    {
        return $this->env;
    }
    public function getParent(array $context)
    {
        return false;
    }
    public function displayParentBlock($name, array $context, array $blocks = array())
    {
        if (false !== $parent = $this->getParent($context)) {
            $parent->displayBlock($name, $context, $blocks);
        } else {
            throw new Twig_Error_Runtime('This template has no parent', -1, $this->getTemplateName());
        }
    }
    public function displayBlock($name, array $context, array $blocks = array())
    {
        if (isset($blocks[$name])) {
            $b = $blocks;
            unset($b[$name]);
            call_user_func($blocks[$name], $context, $b);
        } elseif (isset($this->blocks[$name])) {
            call_user_func($this->blocks[$name], $context, $blocks);
        } elseif (false !== $parent = $this->getParent($context)) {
            $parent->displayBlock($name, $context, array_merge($this->blocks, $blocks));
        }
    }
    public function renderParentBlock($name, array $context, array $blocks = array())
    {
        ob_start();
        $this->displayParentBlock($name, $context, $blocks);
        return new Twig_Markup(ob_get_clean());
    }
    public function renderBlock($name, array $context, array $blocks = array())
    {
        ob_start();
        $this->displayBlock($name, $context, $blocks);
        return new Twig_Markup(ob_get_clean());
    }
    public function hasBlock($name)
    {
        return isset($this->blocks[$name]);
    }
    public function getBlockNames()
    {
        return array_keys($this->blocks);
    }
    public function display(array $context, array $blocks = array())
    {
        try {
            $this->doDisplay($context, $blocks);
        } catch (Twig_Error $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Twig_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, null, $e);
        }
    }
    public function render(array $context)
    {
        ob_start();
        try {
            $this->display($context);
        } catch (Exception $e) {
                                                $count = 100;
            while (ob_get_level() && --$count) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean();
    }
    abstract protected function doDisplay(array $context, array $blocks = array());
    protected function getContext($context, $item)
    {
        if (!array_key_exists($item, $context)) {
            throw new Twig_Error_Runtime(sprintf('Variable "%s" does not exist', $item));
        }
        return $context[$item];
    }
    protected function getAttribute($object, $item, array $arguments = array(), $type = Twig_TemplateInterface::ANY_CALL, $noStrictCheck = false)
    {
                if (Twig_TemplateInterface::METHOD_CALL !== $type) {
            if ((is_array($object) || is_object($object) && $object instanceof ArrayAccess) && isset($object[$item])) {
                return $object[$item];
            }
            if (Twig_TemplateInterface::ARRAY_CALL === $type) {
                if (!$this->env->isStrictVariables() || $noStrictCheck) {
                    return null;
                }
                if (is_object($object)) {
                    throw new Twig_Error_Runtime(sprintf('Key "%s" in object (with ArrayAccess) of type "%s" does not exist', $item, get_class($object)));
                                } else {
                    throw new Twig_Error_Runtime(sprintf('Key "%s" for array with keys "%s" does not exist', $item, implode(', ', array_keys($object))));
                }
            }
        }
        if (!is_object($object)) {
            if (!$this->env->isStrictVariables() || $noStrictCheck) {
                return null;
            }
            throw new Twig_Error_Runtime(sprintf('Item "%s" for "%s" does not exist', $item, $object));
        }
                $class = get_class($object);
        if (!isset(self::$cache[$class])) {
            $r = new ReflectionClass($class);
            self::$cache[$class] = array('methods' => array(), 'properties' => array());
            foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                self::$cache[$class]['methods'][strtolower($method->getName())] = true;
            }
            foreach ($r->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                self::$cache[$class]['properties'][$property->getName()] = true;
            }
        }
                if (Twig_TemplateInterface::METHOD_CALL !== $type) {
            if (isset(self::$cache[$class]['properties'][$item]) || isset($object->$item)) {
                if ($this->env->hasExtension('sandbox')) {
                    $this->env->getExtension('sandbox')->checkPropertyAllowed($object, $item);
                }
                return $object->$item;
            }
        }
                $lcItem = strtolower($item);
        if (isset(self::$cache[$class]['methods'][$lcItem])) {
            $method = $item;
        } elseif (isset(self::$cache[$class]['methods']['get'.$lcItem])) {
            $method = 'get'.$item;
        } elseif (isset(self::$cache[$class]['methods']['is'.$lcItem])) {
            $method = 'is'.$item;
        } elseif (isset(self::$cache[$class]['methods']['__call'])) {
            $method = $item;
        } else {
            if (!$this->env->isStrictVariables() || $noStrictCheck) {
                return null;
            }
            throw new Twig_Error_Runtime(sprintf('Method "%s" for object "%s" does not exist', $item, get_class($object)));
        }
        if ($this->env->hasExtension('sandbox')) {
            $this->env->getExtension('sandbox')->checkMethodAllowed($object, $method);
        }
        $ret = call_user_func_array(array($object, $method), $arguments);
        if ($object instanceof Twig_TemplateInterface) {
            return new Twig_Markup($ret);
        }
        return $ret;
    }
}
}
namespace Monolog\Formatter
{
interface FormatterInterface
{
    function format(array $record);
}
}
namespace Monolog\Formatter
{
use Monolog\Logger;
class LineFormatter implements FormatterInterface
{
    const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
    const SIMPLE_DATE = "Y-m-d H:i:s";
    protected $format;
    protected $dateFormat;
    public function __construct($format = null, $dateFormat = null)
    {
        $this->format = $format ?: self::SIMPLE_FORMAT;
        $this->dateFormat = $dateFormat ?: self::SIMPLE_DATE;
    }
    public function format(array $record)
    {
        $vars = $record;
        $vars['datetime'] = $vars['datetime']->format($this->dateFormat);
        $output = $this->format;
        foreach ($vars as $var => $val) {
            if (is_array($val)) {
                $strval = array();
                foreach ($val as $subvar => $subval) {
                    $strval[] = $subvar.': '.$subval;
                }
                $replacement = $strval ? $var.'('.implode(', ', $strval).')' : '';
                $output = str_replace('%'.$var.'%', $replacement, $output);
            } else {
                $output = str_replace('%'.$var.'%', $val, $output);
            }
        }
        foreach ($vars['extra'] as $var => $val) {
            $output = str_replace('%extra.'.$var.'%', $val, $output);
        }
        $record['message'] = $output;
        return $record;
    }
}
}
namespace Monolog\Handler
{
use Monolog\Logger;
class FingersCrossedHandler extends AbstractHandler
{
    protected $handler;
    protected $actionLevel;
    protected $buffering = true;
    protected $bufferSize;
    protected $buffer = array();
    public function __construct($handler, $actionLevel = Logger::WARNING, $bufferSize = 0, $bubble = false)
    {
        $this->handler = $handler;
        $this->actionLevel = $actionLevel;
        $this->bufferSize = $bufferSize;
        $this->bubble = $bubble;
    }
    public function handle(array $record)
    {
        if ($this->buffering) {
            $this->buffer[] = $record;
            if ($this->bufferSize > 0 && count($this->buffer) > $this->bufferSize) {
                array_shift($this->buffer);
            }
            if ($record['level'] >= $this->actionLevel) {
                $this->buffering = false;
                if (!$this->handler instanceof HandlerInterface) {
                    $this->handler = call_user_func($this->handler, $record, $this);
                }
                if (!$this->handler instanceof HandlerInterface) {
                    throw new \RuntimeException("The factory callback should return a HandlerInterface");
                }
                foreach ($this->buffer as $record) {
                    $this->handler->handle($record);
                }
                $this->buffer = array();
            }
        } else {
            $this->handler->handle($record);
        }
        return false === $this->bubble;
    }
    public function reset()
    {
        $this->buffering = true;
    }
    protected function write(array $record)
    {
        throw new \BadMethodCallException('This method should not be called directly on the FingersCrossedHandler.');
    }
}}
namespace JMS\SecurityExtraBundle\Mapping\Driver
{
use Doctrine\Common\Annotations\Lexer;
use Doctrine\Common\Annotations\Parser;
class AnnotationParser extends Parser
{
    private static $strippedTags = array(
        "{@internal", "{@inheritdoc", "{@link"
    );
    public function parse($docBlockString, $context='')
    {
                $input = str_replace(self::$strippedTags, '', $docBlockString);
                if (!preg_match('/^\s*\*\s*(@.*)/ms', $input, $match)) {
            return array();
        }
        return parent::parse($match[1], $context);
    }
    public function Annotations()
    {
        $this->isNestedAnnotation = false;
        $annotations = array();
        $annot = $this->Annotation();
        if ($annot !== false) {
            $annotations[] = $annot;
            $this->getLexer()->skipUntil(Lexer::T_AT);
        }
        while ($this->getLexer()->lookahead !== null && $this->getLexer()->isNextToken(Lexer::T_AT)) {
            $this->isNestedAnnotation = false;
            $annot = $this->Annotation();
            if ($annot !== false) {
                $annotations[] = $annot;
                $this->getLexer()->skipUntil(Lexer::T_AT);
            }
        }
        return $annotations;
    }
}}
namespace JMS\SecurityExtraBundle\Mapping\Driver
{
use JMS\SecurityExtraBundle\Annotation\RunAs;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;
use JMS\SecurityExtraBundle\Annotation\SecureReturn;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Mapping\MethodMetadata;
class AnnotationConverter
{
    public function convertMethodAnnotations(\ReflectionMethod $method, array $annotations)
    {
        $parameters = array();
        foreach ($method->getParameters() as $index => $parameter) {
            $parameters[$parameter->getName()] = $index;
        }
        $methodMetadata = new MethodMetadata($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Secure) {
                $methodMetadata->setRoles($annotation->getRoles());
            } else if ($annotation instanceof SecureParam) {
                if (!isset($parameters[$annotation->getName()])) {
                    throw new \InvalidArgumentException(sprintf('The parameter "%s" does not exist for method "%s".', $annotation->getName(), $method->getName()));
                }
                $methodMetadata->addParamPermissions($parameters[$annotation->getName()], $annotation->getPermissions());
            } else if ($annotation instanceof SecureReturn) {
                $methodMetadata->addReturnPermissions($annotation->getPermissions());
            } else if ($annotation instanceof SatisfiesParentSecurityPolicy) {
                $methodMetadata->setSatisfiesParentSecurityPolicy();
            } else if ($annotation instanceof RunAs) {
                $methodMetadata->setRunAsRoles($annotation->getRoles());
            }
        }
        return $methodMetadata;
    }
}}
namespace JMS\SecurityExtraBundle\Security\Authorization\Interception
{
class MethodInvocation extends \ReflectionMethod
{
    private $arguments;
    private $object;
    public function __construct($class, $name, $object, array $arguments = array())
    {
        parent::__construct($class, $name);
        if (!is_object($object)) {
            throw new \InvalidArgumentException('$object must be an object.');
        }
        $this->arguments = $arguments;
        $this->object = $object;
    }
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getThis()
    {
        return $this->object;
    }
}}
