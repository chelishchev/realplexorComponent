realplexorComponent
===================

Простая обертка для Yii над Realplexor'ом

Пример использования
===================

main.php

        'broadcast' => array(
            'class'     => 'RealplexorComponent',
            'host'      => '127.0.0.1',
            'port'      => '10010',
            'namespace' => 'adminspace',
            'url'       => 'http://rpl.example.com',
        ),
        
layout/main.php (показ сообщений в шаблоне)

    Y::broadcast()->listen('tickets', <<<JS
      js: function(data, id, cursor){
          msg_stack(data.text, 'info', null);          
       }
    JS
    );`


send code:


            Yii::app()->broadcast()->send('tickets', array(
                                                 'text' => 'Поступила новая заявка: ' . CHtml::link('перейти', Y::a()->createUrl('/sys/ticket/view', array('id' => $this->id)))
                                            ));
