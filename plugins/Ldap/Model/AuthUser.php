 <?php 
class AuthUser extends AppModel{
    public $name = 'AuthUser';
    public $useTable = false;

    private $domain;
    private $host;
    private $port;
    private $baseDn;
    private $user;
    private $email;
    private $pass;

    private $ds;



    public function login($email, $password){
        $this->domain = LDAP_DOMAIN;
        $this->host = LDAP_HOST;
        $this->port = LDAP_PORT;
        $this->baseDn = LDAP_BASE_DN;

        $this->user = preg_replace('/@.*/', '', $email);
        $this->email = "{$this->user}@{$this->domain}";
        $this->pass = $password;

        $this->ds = ldap_connect($this->host, $this->port);
        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);

        return ldap_bind($this->ds, $this->email, $this->pass);
    }

    public function getUser(){
        $map = $this->findAll(array('conditions' => "(samaccountname={$this->user})"));
        return $map[0];
    }



    public function findAll($conditions=array()){
        $defaults = array(
            'fields' => array(
                'userprincipalname',
                'samaccountname',
                'displayName',
                'givenName',
                // 'lastlogontimestamp',
                'cn',
                'sn',
                ),
            );
        $opt = array_merge($defaults, $conditions);

        $rst = ldap_search($this->ds, $this->baseDn, $opt['conditions'], $opt['fields']);

        if ($rst){
            ldap_sort($this->ds, $rst, "sn");

            return ldap_get_entries($this->ds, $rst);
        }
    }   

    public function __destruct(){
        ldap_close($this->ds);
    } 

}
?> 