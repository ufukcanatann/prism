<?php

namespace App\Http\Controllers;

use Core\View\AdvancedView;
use Core\Session;
use Core\Database;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected $view;
    protected $session;
    protected $db;
    protected $redirectUrl;

    public function __construct()
    {
        $this->view = container()->make(AdvancedView::class);
        $this->session = new Session();
        $this->db = new Database();
    }

    protected function view($name, $data = [])
    {
        try {
            $content = $this->view->render($name, $data);
            return new Response($content);
        } catch (\Exception $e) {
            // Debug modunda hata detaylarını göster
            if (config('app.debug')) {
                return new Response('View Error: ' . $e->getMessage(), 500);
            }
            return new Response('View not found', 500);
        }
    }

    protected function json($data, $status = 200)
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    protected function redirect($path)
    {
        $url = $path;
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $url = rtrim(config('app.url', 'http://127.0.0.1'), '/') . '/' . ltrim($path, '/');
        }
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
    }

    protected function with($key, $value)
    {
        $this->session->set($key, $value);
        return $this->redirect($this->redirectUrl ?? '/');
    }

    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return new \Symfony\Component\HttpFoundation\RedirectResponse($referer);
    }

    protected function validate($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleArray = explode('|', $rule);
            
            foreach ($ruleArray as $singleRule) {
                if ($singleRule === 'required' && empty($data[$field])) {
                    $errors[$field][] = "The {$field} field is required.";
                }
                
                if (strpos($singleRule, 'min:') === 0) {
                    $min = (int) substr($singleRule, 4);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field][] = "The {$field} must be at least {$min} characters.";
                    }
                }
                
                if (strpos($singleRule, 'max:') === 0) {
                    $max = (int) substr($singleRule, 4);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field][] = "The {$field} may not be greater than {$max} characters.";
                    }
                }
                
                if ($singleRule === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} must be a valid email address.";
                }
            }
        }
        
        if (!empty($errors)) {
            Session::set('errors', $errors);
            Session::set('old', $data);
            return false;
        }
        
        return true;
    }

    protected function errors()
    {
        return Session::get('errors', []);
    }

    protected function hasErrors()
    {
        return !empty($this->errors());
    }
}
