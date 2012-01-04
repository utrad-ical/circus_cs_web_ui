<?php

class SeriesFilterTest extends PHPUnit_Framework_TestCase
{
	private $filter;
	private $data;

	public function setUp() {
		require_once('../app/lib/SeriesFilter.class.php');
		$this->filter = new SeriesFilter();
	}

	private function doTestNode($json, $require = true)
	{
		$node = json_decode($json, true);
		$this->assertEquals($this->filter->processFilterNode($this->data, $node), $require);
	}

	public function testComparisonNodeString()
	{
		$this->data = array('st' => 'abcdefg', 'dummy' => 'hoge');

		$this->doTestNode('{"key":"st", "value":"abcdefg"}', true);
		$this->doTestNode('{"key":"st", "value":"abcdefg", "condition":"="}', true);
		$this->doTestNode('{"key":"st", "value":"abcdd", "condition":"!="}', true);
		$this->doTestNode('{"key":"st", "value":"zzz"}', false);

		$this->doTestNode('{"key":"st", "value":"abc", "condition": "^="}', true);
		$this->doTestNode('{"key":"st", "value":"abc", "condition": "*="}', true);
		$this->doTestNode('{"key":"st", "value":"abc", "condition": "$="}', false);

		$this->doTestNode('{"key":"st", "value":"cde", "condition": "^="}', false);
		$this->doTestNode('{"key":"st", "value":"cde", "condition": "*="}', true);
		$this->doTestNode('{"key":"st", "value":"cde", "condition": "$="}', false);

		$this->doTestNode('{"key":"st", "value":"efg", "condition": "^="}', false);
		$this->doTestNode('{"key":"st", "value":"efg", "condition": "*="}', true);
		$this->doTestNode('{"key":"st", "value":"efg", "condition": "$="}', true);
	}

	public function testComparisonNodeNumeral()
	{
		$this->data = array('no' => 120, 'fl' => 3.14);

		$this->doTestNode('{"key":"no", "value":120}', true);
		$this->doTestNode('{"key":"no", "value":121, "condition":"!="}', true);
		$this->doTestNode('{"key":"no", "value":121, "condition":"<>"}', true);
		$this->doTestNode('{"key":"no", "value":120, "condition":"="}', true);

		$this->doTestNode('{"key":"no", "value":121, "condition":"<"}', true);
		$this->doTestNode('{"key":"no", "value":121, "condition":"<="}', true);
		$this->doTestNode('{"key":"no", "value":100, "condition":">"}', true);
		$this->doTestNode('{"key":"no", "value":121, "condition":">="}', false);

		$this->doTestNode('{"key":"fl", "value":3.1415, "condition":"<"}', true);
		$this->doTestNode('{"key":"fl", "value":3.14, "condition":"="}', true);

	}

	public function testGroupNode()
	{
		$this->data = array('apple'=>'red','banana'=>'yellow','leaf'=>'green','sky'=>'blue');

		$this->doTestNode('{"group":"and", "members":[{"key":"apple","value":"red"}]}', true);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"yellow"},{"key":"apple","value":"red"}]}', true);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"yellow"},{"key":"apple","value":"pink"}]}', false);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"green"},{"key":"apple","value":"red"}]}', false);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"green"},{"key":"apple","value":"blue"}]}', false);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"yellow"},
			{"key":"apple","value":"red"},{"key":"sky","value":"blue"},{"key":"leaf","value":"green"}]}', true);

		$this->doTestNode('{"members":[{"key":"banana","value":"yellow"},{"key":"apple","value":"red"}]}', true);

		$this->doTestNode('{"group":"or", "members":[{"key":"apple","value":"red"}]}', true);
		$this->doTestNode('{"group":"or", "members":[{"key":"banana","value":"yellow"},{"key":"apple","value":"red"}]}', true);
		$this->doTestNode('{"group":"or", "members":[{"key":"banana","value":"yellow"},{"key":"apple","value":"pink"}]}', true);
		$this->doTestNode('{"group":"or", "members":[{"key":"banana","value":"green"},{"key":"apple","value":"red"}]}', true);
		$this->doTestNode('{"group":"and", "members":[{"key":"banana","value":"green"},{"key":"apple","value":"blue"}]}', false);
	}

	public function testNestedGroupNode()
	{
		$this->data = array('apple'=>'red','banana'=>'yellow','leaf'=>'green','sky'=>'blue');

		$n ='
		{
			"group":"and",
			"members":[
				{
					"group": "and",
					"members": [
						{"key":"sky", "value":"blue"},
						{"key":"leaf", "value":"gree", "condition":"^="}
					]
				},
				{
					"group": "and",
					"members": [
						{"key":"apple", "value":"ed", "condition":"$="},
						{"key":"banana", "value":"black", "condition":"!="}
					]
				}
			]
		}';
		$this->doTestNode($n, true);

	}

	public function testRuleSet()
	{
		$data = array('apple'=>'red','banana'=>'yellow','leaf'=>'green','sky'=>'blue');
		$n = '
		{
			"filter": { "key":"apple", "value":"red" },
			"rule": { "out": 150 }
		}';
		$ruleset = json_decode($n, true);
		$this->assertNotNull($ruleset);

		$this->assertEquals(
			$this->filter->processOneRuleSet($data, $ruleset),
			array("out" => 150)
		);
	}

	private function doTestRuleSets($rulelist, $a, $b, $require = false)
	{
		$data = array('a'=>$a, 'b'=>$b);
		$result = $this->filter->processRuleSets($data, $rulelist);
		$this->assertEquals($result['out'], $require);
	}

	public function testRuleSets()
	{
		$n = '
		[
			{
				"filter": { "key":"a", "value":150 },
				"rule": { "out": "result-A" }
			},
			{
				"filter": { "key":"b", "value":50 },
				"rule": { "out": "result-B" }
			},
			{
				"filter": { "members": [ { "key":"a", "value":30 }, { "key":"b", "value":-15 } ] },
				"rule": { "out": "result-C" }
			},
			{
				"filter": { "group":"or", "members": [ { "key":"a", "value":100 }, { "key":"b", "value":70 } ] },
				"rule": { "out": "result-D" }
			}
		]';
		$rulelist = json_decode($n, true);
		$this->assertNotNull($rulelist);
		$this->doTestRuleSets($rulelist, 150, 20, 'result-A');
		$this->doTestRuleSets($rulelist, 100, 50, 'result-B');
		$this->doTestRuleSets($rulelist, 30, -15, 'result-C');
		$this->doTestRuleSets($rulelist, "150", 20, 'result-A');
		$this->doTestRuleSets($rulelist, 33, "50", 'result-B');
		$this->doTestRuleSets($rulelist, 33, "70", 'result-D');
		$this->doTestRuleSets($rulelist, 100, "70", 'result-D');
		$this->doTestRuleSets($rulelist, 50, 30, false);
	}
}
