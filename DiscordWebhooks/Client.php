<?php

namespace DiscordWebhooks;

/**
 * Client generates the payload and sends the webhook payload to Discord
 */
class Client
{
    /**
     * URL do WebHook
     *
     * @var string
     */
    protected $url;

    /**
     * Nome de usuário 
     *
     * @var string
     */
    protected $username;

    /**
     * URL da imagem do usuário
     *
     * @var string
     */
    protected $avatar;

    /**
     * Mensagem a ser enviada
     *
     * @var string
     */
    protected $message;

    /**
     * Conteúdo personalizado
     *
     * @var Embed
     */
    protected $embeds;
    
    protected $tts;
    
    /**
     * Última resposta recebida do Servidor
     *
     * @var array
     */
    protected $last_response;

    /**
     * Últimos dados enviados para o servidor
     *
     * @var array
     */
    protected $last_request;

    /**
     * Flag para a verificação SSL da conexão
     *
     * @var array
     */
    protected $ssl_option = 0;

    /**
     * Construtor da classe
     *
     * @param string $url URL do WebHook
     */
    public function __construct($url)
    {
        $this->setURL($url);
    }

    /**
     * Liga a configuração de verificação do certificado SSL
     *
     * @return void
     */
    public function sslOn()
    {
        $this->ssl_option = 1;
    }

    /**
     * Desliga a configuração de verificação do certificado SSL
     *
     * @return void
     */
    public function sslOff()
    {
        $this->ssl_option = 0;
    }

    /**
     * Configura a URL do WebHook
     *
     * @param string $url URL do Webhook
     * 
     * @return void
     */
    public function setURL($url)
    {
        if (!isset($url)) {
            throw new \Exception('URL empty');
        }

        $this->url = $url;
    }

    public function tts($tts = false)
    {
        $this->tts = $tts;
        return $this;
    }

    /**
     * Configura o Nome de usuário do Bot
     *
     * @param string $username Nome de Usuário
     * 
     * @return Client
     */
    public function username($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Configura o Avatar do usuário
     *
     * @param string $new_avatar Novo Avatar para o usuário
     * 
     * @return Client
     */
    public function avatar($new_avatar)
    {
        $this->avatar = $new_avatar;

        return $this;
    }

    /**
     * Configura uma mensagem a ser enviada
     *
     * @param string $new_message Mensagem a ser enviada para o canal
     * 
     * @return Client
     */
    public function message($new_message)
    {
        $this->message = $new_message;

        return $this;
    }

    /**
     * Configura o conteúdo do embed a ser enviado junto da mensagem
     *
     * @param Embed $embed Instanância do Embed
     * 
     * @return Client
     */
    public function embed($embed)
    {
        $this->embeds[] = $embed->toArray();

        return $this;
    }

    /**
     * Consolida os dados configurados na classe, e cria o objeto de requisição
     *
     * @return object
     */
    private function _makePayLoad()
    {
        $payload = json_encode(
            array(
                'username' => $this->username,
                'avatar_url' => $this->avatar,
                'content' => $this->message,
                'embeds' => $this->embeds,
                'tts' => $this->tts,
            )
        );

        return $payload;
    }

    /**
     * Envia a requisição para o WebHook
     *
     * @return Client
     */
    public function send()
    {
      
        $payload = $this->_makePayLoad();

        $this->last_request = $payload;

        $ch = curl_init();

        $options = array();

        $options += array(
            CURLOPT_URL => $this->url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $payload
        );

        $options += array(
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => $this->ssl_option
        );

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            throw new \Exception("cURL error ({$errno}):\n {$error_message}");
        }

        $json_result = json_decode($result, true);

        $this->last_response = $json_result;

        if (($httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 204) {
            throw new \Exception($httpcode . ':' . $result);
        }

        curl_close($ch);

        return $this;
    }

    /**
     * Retorna a última resposta do servidor
     *
     * @return array
     */
    public function lastResponse()
    {
        return $this->last_response;
    }

    /**
     * Retorna os dados da última requisição ao servidor
     *
     * @return array
     */
    public function lastRequest()
    {
        return $this->last_request;
    }
}
