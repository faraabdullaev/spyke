<?php

class m140623_062114_add_nn_tables extends CDbMigration {

	public function up(){
		$this->createTable('{{node_hd}}',
			[	'id' => 'pk',
				'create_key' => 'integer(10) NOT NULL'
			]);
		$this->createTable('{{word_hd}}',
			[	'id' => 'pk',
				'fromId' => 'integer(10) NOT NULL',
				'toId' => 'integer(10) NOT NULL',
				'strength' => 'float(10) NOT NULL'
			]);
		$this->createTable('{{url_hd}}',
			[	'id' => 'pk',
				'fromId' => 'integer(10) NOT NULL',
				'toId' => 'integer(10) NOT NULL',
				'strength' => 'float(10) NOT NULL'
			]);
	}

	public function down(){
		$this->dropTable('{{node_hd}}');
		$this->dropTable('{{word_hd}}');
		$this->dropTable('{{url_hd}}');
	}

}