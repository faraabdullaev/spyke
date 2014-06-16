<?php

class m140614_113037_add_pageRank_table extends CDbMigration{

	public function up(){
		$this->createTable('{{page_rank}}',
			[
				'Id' => 'pk',
				'urlId' => 'integer(10) NOT NULL',
				'score' => 'float(10) NOT NULL DEFAULT 0.85'
			]
		);
	}

	public function down(){
		$this->dropTable('{{page_rank}}');
	}
}