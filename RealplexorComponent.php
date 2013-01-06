<?php

/**
 * @author Ivan Chelishchev <chelishchev@gmail.com>
 */
Yii::import('application.vendors.Realplexor');
class RealplexorComponent extends CApplicationComponent
{
    public $host = '127.0.0.1';
    public $port = '10010';
    public $namespace = '';
    /**
     * Указывать с http:// в начале. Необходим для доступа к js
     * @var string
     */
    public $url = '';

    /** @var Realplexor */
    protected $_rp = null;

    protected $_registerJS = false;

    public function init()
    {
        parent::init();
        $this->setConfig();
    }

    protected function setConfig()
    {
        if(!$this->host || !$this->port || !$this->url)
        {
            throw new Exception('Where are host or port or url?');
        }

        if (is_null($this->_rp))
        {
            $this->_rp = new Realplexor($this->host, $this->port, $this->namespace);
        }
    }

    /**
     * Send data to realplexor.
     *
     * @param mixed $idsAndCursors    Target IDs in form of: array(id1 => cursor1, id2 => cursor2, ...)
     *                                of array(id1, id2, id3, ...). If sending to a single ID,
     *                                you may pass it as a plain string, not array.
     * @param mixed $data             Data to be sent (any format, e.g. nested arrays are OK).
     * @param array $showOnlyForIds   Send this message to only those who also listen any of these IDs.
     *                                This parameter may be used to limit the visibility to a closed
     *                                number of cliens: give each client an unique ID and enumerate
     *                                client IDs in $showOnlyForIds to not to send messages to others.
     * @throws Dklab_Realplexor_Exception
     * @return void
     */
    public function send($idsAndCursors, $data, $showOnlyForIds = null)
    {
        $this->_rp->send($idsAndCursors, $data, $showOnlyForIds);
        return $this;
    }

    /**
     * Return list of online IDs (keys) and number of online browsers
     * for each ID. (Now "online" means "connected just now", it is
     * very approximate; more precision is in TODO.)
     *
     * @param array $idPrefixes   If set, only online IDs with these prefixes are returned.
     * @return array              List of matched online IDs (keys) and online counters (values).
     */
    public function cmdOnlineWithCounters($idPrefixes = null)
    {
        $this->_rp->cmdOnlineWithCounters($idPrefixes);
        return $this;
    }

    /**
     * Return list of online IDs.
     *
     * @param array $idPrefixes   If set, only online IDs with these prefixes are returned.
     * @return array              List of matched online IDs.
     */
    public function cmdOnline($idPrefixes = null)
    {
        return $this->_rp->cmdOnline($idPrefixes);
    }

    /**
     * Return all Realplexor events (e.g. ID offline/offline changes)
     * happened after $fromPos cursor.
     *
     * @param string $fromPos           Start watching from this cursor.
     * @param array  $idPrefixes        Watch only changes of IDs with these prefixes.
     * @return array                   List of array("event" => ..., "cursor" => ..., "id" => ...).
     */
    public function cmdWatch($fromPos, $idPrefixes = null)
    {
        return $this->_rp->cmdWatch($fromPos, $idPrefixes);
    }

    /**
     * Регистрация основных js-скриптов
     * @return RealplexorComponent
     */
    public function putJS()
    {
        if($this->_registerJS)
        {
            return $this;
        }

        Y::a()->clientScript->registerScriptFile($this->url . '/?identifier=SCRIPT&'. time(), CClientScript::POS_HEAD);
        Y::a()->clientScript->registerScript($this->namespace . 'jsinit', '
            var realplexor = new Dklab_Realplexor(
                "' . $this->url . '/?' . time() . '",  // URL of engine
                "' . $this->namespace . '" // namespace
            );

            ', CClientScript::POS_HEAD
        );

        $this->_registerJS = true;
        return $this;
    }

    /**
     * Вставка js в представление
     * function(data, id, cursor)
     * @param $nameChannel на что подписываемся?
     * @param $func должно быть указано в функции три параметра (data, id, cursor)
     */
    public function listen($nameChannel, $func)
    {
        $this->putJS();
        //function(data, id, cursor)
        $func = CJavaScript::encode($func);
        Y::a()->clientScript->registerScript($nameChannel . 'rp', '
        realplexor.subscribe("' . $nameChannel . '", ' . $func . ');
        realplexor.execute();
        ', CClientScript::POS_END);
    }
}
