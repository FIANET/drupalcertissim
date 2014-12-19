<?php

/**
 * Implement a Fia-Net's service (Certissim, Kwixo or Certissim) *
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method void setName(string $name) sets the local var name
 * @method void setSiteid(string $siteid) sets the local var siteid
 * @method void setLogin(string $login) sets the local var login
 * @method void setPassword(string $password) sets the local var password
 * @method void setPasswordurlencoded(string $passwordurlencoded) sets the local var passwordurlencoded
 * @method void setAuthkey(string $authkey) sets the local var authkey
 * @method void setStatus(string $status) sets the local var status
 * @method string getName(string $name) returns the local var name value
 * @method string getSiteid(string $siteid) returns the local var siteid value
 * @method string getLogin(string $login) returns the local var login value
 * @method string getPassword(string $password) returns the local var password value
 * @method string getPasswordurlencoded(string $passwordurlencoded) returns the local var passwordurlencoded value
 * @method string getAuthkey(string $authkey) returns the local var authkey value
 * @method string getStatus(string $status) returns the local var status value
 * 
 * @method string getUrlScriptname() returns the URL of the script 'Scriptname' according to the status
 * Usage :
 * <code>
 * $service->getUrlStacking(); //returns the stacking.cgi URL
 * </code>
 */


abstract class CertissimService extends CertissimMother {
    /* site params */

    protected $name; //product name
    protected $siteid;
    protected $login;
    protected $password;
    protected $passwordurlencoded;
    protected $authkey;
    protected $status;
    protected $url = array();
    private $_url = array(
        'redirect' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/redirect.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/redirect.cgi',
        ),
        'singet' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/singet.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/singet.cgi',
        ),
        'stacking' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/stacking.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/stacking.cgi',
        ),
        'stackfast' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/stackfast.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/stackfast.cgi',
        ),
        'backoffice' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener',
            'test' => 'https://secure.FIA-NET.com/pprod',
        ),
        'visucheck' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/BO/visucheck_detail.php',
            'test' => 'https://secure.FIA-NET.com/pprod/BO/visucheck_detail.php',
        ),
        'getvalidation' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_validation.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/get_validation.cgi',
        ),
        'redirectvalidation' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/redirect_validation.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/redirect_validation.cgi',
        ),
        'getvalidstack' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_validstack.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/get_validstack.cgi',
        ),
        'getalert' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/engine/get_alert.cgi',
            'test' => 'https://secure.FIA-NET.com/pprod/engine/get_alert.cgi',
        ),
        'visucheckdetail' => array(
            'prod' => 'https://secure.FIA-NET.com/fscreener/BO/visucheck_detail.php',
            'test' => 'https://secure.FIA-NET.com/pprod/BO/visucheck_detail.php',
        )
    );
    
    
    private $_param_names = array(
        'siteid',
        'login',
        'password',
        'status',
    );
    
    private $_available_statuses = array(
        'test',
        'prod',
    );

    public function __construct() {
        //loads site params
        $this->loadParams('site_params.yml');
        //loads webservices URL
        $this->loadURLs();
    }

    public function getProductname() {
        $name = $this->getName();
        if (empty($name))
            $this->setName(strtolower(get_class($this)));

        return $this->getName();
    }

    /**
     * loads site params from the file given in param
     * 
     * @param string $filename
     */
    private function loadParams($filename) {

        //gets paras from the file
    //$siteparams = Spyc::YAMLLoad(ROOT_DIR . '/lib/' . $this->getProductname() . '/const/' . $filename);
    $siteparams = Spyc::YAMLLoad(CERTISSIM_ROOT_DIR . '/lib/sac/const/' . $filename);
    //reads all params and stores each one localy
    foreach ($siteparams as $key => $value) {
      $funcname = "set$key";
      $this->$funcname($value);
    }
    }

    /**
     * loads scripts URL according to the current status if status defined and active
     */
    private function loadURLs() {

        $status = $this->statusIsAvailable($this->getStatus()) ? $this->getStatus() : 'test';

        foreach ($this->_url as $scriptname => $modes) {
            $this->url[$scriptname] = $modes[$status];
        }
    }

    /**
     * returns the URL of the script given in param if it exists, false otherwise
     *
     * @param string $script
     * @return string
     */
    public function getUrl($script) {
        if (!array_key_exists($script, $this->url)) {
            $msg = "L'url pour le script $script n'existe pas ou n'est pas chargée. Vérifiez le paramétrage.";
            CertissimLogger::insertLogCertissim(__METHOD__ . ' : ' . __LINE__, $msg);
            return false;
        }

        return $this->url[$script];
    }

    /**
     * switches status to $mode and reload URL if available, returns false otherwise
     *
     * @param string $mode test OR prod OR off
     * @return bool
     */
    public function switchMode($mode) {
        if (!$this->statusIsAvailable($mode)) {
            CertissimLogger::insertLogCertissim(__FILE__, "Le mode '$mode' n'est pas reconnu.");
            $mode = 'test';
        }

        //switch the status to $mode
        $this->setStatus($mode);

        //reload URLs
        $this->loadURLs();
    }

    /**
     * saves params into the param YAML file and returns true if save succeed, false otherwise
     *
     * @return bool
     */
    public function saveParamInFile() {
       // $siteparams = Spyc::YAMLLoad(CERTISSIM_ROOT_DIR.'/lib/sac/const/site_params.yml');

        //edits an array containing the current object's params
       /* foreach (array_keys($siteparams) as $param) {
            $funcname = "get$param";
            $newparams[$param] = $this->$funcname();
        }*/
        
        $newparams['siteid'] = variable_get('uc_certissim_siteid');
        $newparams['login'] = variable_get('uc_certissim_login');
        $newparams['password'] = variable_get('uc_certissim_password');
        $newparams['status'] = variable_get('uc_certissim_status');

        //creates a yml string from the currect object's params
        $yaml_string = Spyc::YAMLDump($newparams);
        //opens the YAML file and puts the cursor at the beginning
       // $handle = fopen(CERTISSIM_ROOT_DIR . '/lib/' . $this->getProductname() . '/const/site_params.yml', 'w');
        $handle = fopen(CERTISSIM_ROOT_DIR . '/lib/sac/const/site_params.yml', 'w');
        //writes new params into the opened file
        $written = @fwrite($handle, $yaml_string);
        fclose($handle);

        return $written;
    }

    public function __call($name, array $params) {
        if (preg_match('#^getUrl.+$#', $name) > 0) {
            return $this->getUrl(preg_replace('#^getUrl(.+)$#', '$1', $name));
        }

        return parent::__call($name, $params);
    }

}