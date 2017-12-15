<?php
class QueryBuilder extends Crud{
	const JOIN_AND = " AND ";
	const JOIN_OR = " OR ";
	
	const OP_EQUAL = " = ";
	const OP_IN = " IN ";
	const OP_LIKE = " ILIKE ";
	
	const ORDER_ASC = " ASC";
	const ORDER_DESC = " DESC";
	
	const ALL = "*";
	
	private $_what;
	private $_from;
	private $_where;
	private $_orderBy;

	public function __construct(iDBConnector $DBConnector){
		parent::__construct($DBConnector);
		return $this;
	}
		
	public function select($what = self::ALL){
		$this->_what = $what;
		return $this;
	}
	
	public function from($from){
		$this->_from = $from;
		return $this;
	}
	
	public function orderBy($orderBy, $order = self::ORDER_ASC){
		$this->_orderBy = $orderBy . $order;
	}
	
	public function opEqual($key, $value){
		$this->_where .= $key . self::OP_EQUAL . $value;
		return $this;
	}
	
	public function opIn($key, $value){
		$this->_where .= $key . self::OP_IN . "( $value )";
		return $this;
	}
	
	public function opLike($key, $value){
		$this->_where .= $key . self::OP_LIKE . "'%$value%'";
		return $this;
	}
	
	public function joinAnd(/*$leftCondition, $rightCondition*/){
		$this->_where .= self::JOIN_AND;
		return $this;
	}
	
	public function joinOr(/*$leftCondition, $rightCondition*/){
		$this->_where .= self::JOIN_OR;
		return $this;
	}
	
	public function openBracket(){
		$this->_where .= "(";
		return $this;
	}
	
	public function closeBracket(){
		$this->_where .= ")";
		return $this;
	}
	
	public function reset(){
		$this->_where = "";
		return $this;
	}
	
	public function getList(){
		return $this->getGeneric($this->_what, $this->_from, $this->_where, $this->_orderBy);
	}
	
	public function searchBy($filters, $join = " AND ", $orderColumn = null){
		$orderColumn = is_null($orderColumn) ? $this->ORDER_COL : $orderColumn;
		if(empty($filters))
			return $this->getAll($orderColumn);
		$where = [];
		$and="";
		foreach($filters as $data){
				
			if(!isset($data[self::SEARCHBY_OPERATOR])){
				$data[self::SEARCHBY_OPERATOR] = "=";
			}
			if(is_array($data[self::SEARCHBY_VALUE])){
				$data[self::SEARCHBY_OPERATOR] = "in";
				$data[self::SEARCHBY_VALUE]= array_map( "Utils::apici",$data[self::SEARCHBY_VALUE] /*,array(true)*/);
				$data[self::SEARCHBY_VALUE] = sprintf("( %s )", join(", ", $data[self::SEARCHBY_VALUE]));
			}else{
				$data[self::SEARCHBY_VALUE]=Utils::apici($data[self::SEARCHBY_VALUE]);
			}
			if(!isset($data[self::SEARCHBY_OPEN_BRACKET])&&
					!isset($data[self::SEARCHBY_CLOSE_BRACKET])&&
					!isset($data[self::SEARCHBY_INSIDE_BRACKET])&&
					!isset($data [self::SEARCHBY_INSIDE_OPERATOR])){
				array_push($where, $data[self::SEARCHBY_FIELD]. " ".$data[self::SEARCHBY_OPERATOR]. " ".$data[self::SEARCHBY_VALUE]);
			}else{
				if(isset($data[self::SEARCHBY_OPEN_BRACKET])){
					if(isset($data[self::SEARCHBY_FIELD])){
						$and=$and."(". $data[self::SEARCHBY_FIELD]. " ".$data[self::SEARCHBY_OPERATOR]. " ".$data[self::SEARCHBY_VALUE]." ";
					}else{
						$and=$and."( ";
					}
				}
				if(isset($data[self::SEARCHBY_INSIDE_BRACKET])){
					$and=$and." ".$data[self::SEARCHBY_FIELD]. " ".$data[self::SEARCHBY_OPERATOR]. " ".$data[self::SEARCHBY_VALUE]." ";
				}
				if(isset($data[self::SEARCHBY_CLOSE_BRACKET])){
					if(isset($data[self::SEARCHBY_FIELD])){
						$and=$and.$data[self::SEARCHBY_FIELD]. " ".$data[self::SEARCHBY_OPERATOR]. " ".$data[self::SEARCHBY_VALUE]." ) ";
					}else{
						$and = rtrim($and,$data[self::SEARCHBY_OPERATOR]." ");
						$and=$and." ) ";
					}
				}
				if(isset($data[self::SEARCHBY_INSIDE_OPERATOR])){
					$and=$and.$data[self::SEARCHBY_INSIDE_OPERATOR]." ";
				}
				if(isset($data[self::SEARCHBY_END_BRACKET])){
					array_push($where, $and);
					$and="";
				}
			}
		}
		$where = join($join,$where). (!empty($orderColumn) ? " order by ".$orderColumn : "");
		$sql = sprintf($this->SQL_GET, $this->_canIUseView() ? $this->VIEW : $this->TABLE, $where);
		Utils::printr($sql);
		$this->_connInstance->query($sql);
		return $this->_connInstance->allResults();
	}
}

//SELECT * FROM search_master_documents_data_view
//WHERE key = 'note' AND ( value ilike '%Mul%' OR  value ilike '%Ber%' )
//  AND (key = 'acronimo progetto' AND value in ( 'DISVA' ) )
// order by id_md

$qb = new QueryBuilder(DBConnector::getInstance());
$qb
	->reset()
	->select()
	->from("search_master_documents_data_view")
	->opEqual("key", Utils::apici("note"))
	->joinAnd()
	->openBracket()
		->opLike("value", "Mul")
		->joinOr()
		->opLike("value", "Ber")
	->closeBracket()
	->joinAnd()
	->openBracket()
		->opEqual("key", Utils::apici("acronimo progetto"))
		->joinAnd()
		->opIn("value", "DISVA")
		->closeBracket()
	->orderBy("id_md");

Utils::printr($qb->getList());

die("??");
