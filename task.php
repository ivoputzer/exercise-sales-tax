<?php header('content-type: text/plain;charset=utf-8'); error_reporting(1);


	class item
	{
		private $name = null, $price = 0, $quantity = 0, $taxes = 0;

		public function __get ( $n ) // overloading getters to have readonly access on private properties
		{
			return isset ( $this->{$n} ) ? $this->{$n} : null;
		} 

		public function __construct ($name, $price, $excempt = false, $imported = false, $quantity = 1)
		{
			$this->name = $name; $this->price = $price; $this->quantity = $quantity;

			# Basic sales tax is applicable at a rate of 10% except books, food, and medical products that are exempt

			# additional sales tax applicable on all imported goods at a rate of 5%

			$this->taxes = $this->taxes (($excempt ? 0 : 0.1) + ($imported ? 0.05 : 0), $price, $quantity);

			$this->total = $quantity * ($this->taxes + $price);
		}

		private function taxes ($rate)
		{
			# The rounding rules for sales tax are that for a tax rate of n%, a shelf price of p 

			# contains (np/100 rounded up to the nearest 0.05) amount of sales tax.

			return ceil ( $rate * $this->quantity * $this->price * 20 ) / 20; // round to next nickel
		}

		public function __toString () // to concatenate items directly while printing the receipt
		{
			# items and their price (including tax) ... // rounding needs to be done at this stage already

			return sprintf ('%b %s: %0.2f', $this->quantity, $this->name, $this->total);
		}
	}


	class cart
	{
		private $name = null, $items = array();

		public function __construct ( $name = '' )
		{
			$this->name = $name;
		}

		public function add ( item $item )
		{
			array_push ($this->items, $item);
		}

		private function taxes ()
		{
			for ( $sum = 0, $i = count($this->items); $i >= 0; $sum += $this->items[--$i]->taxes );

			return $sum;
		}

		private function total ()
		{
			for ( $sum = 0, $i = count($this->items); $i >= 0; $sum += $this->items[--$i]->total );

			return $sum;
		}

		public function receipt ()
		{
			// receipt which lists the name of all the items and their price (including tax), 

			// finishing with the total cost of the items, and the total amounts of sales taxes paid
			
			return sprintf("%s:\n%s\nSales Taxes: %0.2f\nTotal: %0.2f", $this->name, implode ("\n", $this->items), $this->taxes(), $this->total() );			
		}

		public function __toString ()
		{
			return $this->receipt();
		}
	}




/*
	Input 1:
	1 book at 12.49
	1 music CD at 14.99
	1 chocolate bar at 0.85

	>> expected >>

	Output 1:
	1 book : 12.49
	1 music CD: 16.49
	1 chocolate bar: 0.85
	Sales Taxes: 1.50
	Total: 29.83
*/

	$expected = "Output 1:\n1 book: 12.49\n1 music CD: 16.49\n1 chocolate bar: 0.85\nSales Taxes: 1.50\nTotal: 29.83";

	$cart1 = new cart ('Output 1');

	$cart1->add( new item('book', 12.49, true, false) );
			
	$cart1->add( new item('music CD', 14.99, false, false) );
		
	$cart1->add( new item('chocolate bar', 0.85, true, false) );

	assert_receipts_are_equal ('First Cart Test', $expected, $cart1->receipt() ); // echo $cart1;


/*
	Input 2:
	1 imported box of chocolates at 10.00
	1 imported bottle of perfume at 47.50


	>> expected >>

	Output 2:
	1 imported box of chocolates: 10.50
	1 imported bottle of perfume: 54.65
	Sales Taxes: 7.65
	Total: 65.15
*/

	$expected = "Output 2:\n1 imported box of chocolates: 10.50\n1 imported bottle of perfume: 54.65\nSales Taxes: 7.65\nTotal: 65.15";

	$cart2 = new cart ('Output 2');

	$cart2->add( new item('imported box of chocolates', 10.00, true, true) );
		
	$cart2->add( new item('imported bottle of perfume', 47.50, false, true) );

	assert_receipts_are_equal ('Second Cart Test', $expected, $cart2->receipt() ); // echo $cart2;



/*
	Input 3:
	1 imported bottle of perfume at 27.99
	1 bottle of perfume at 18.99
	1 packet of headache pills at 9.75
	1 box of imported chocolates at 11.25

	>> expected >>

	Output 3:
	1 imported bottle of perfume: 32.19
	1 bottle of perfume: 20.89
	1 packet of headache pills: 9.75
	1 imported box of chocolates: 11.85
	Sales Taxes: 6.70
	Total: 74.68
*/

	$expected = "Output 3:\n1 imported bottle of perfume: 32.19\n1 bottle of perfume: 20.89\n1 packet of headache pills: 9.75\n1 imported box of chocolates: 11.85\nSales Taxes: 6.70\nTotal: 74.68";

	$cart3 = new cart ('Output 3');

	$cart3->add( new item('imported bottle of perfume', 27.99, false, true) );
			
	$cart3->add( new item('bottle of perfume', 18.99, false, false) );
		
	$cart3->add( new item('packet of headache pills', 9.75, true, false) );

	$cart3->add( new item('imported box of chocolates', 11.25, true, true) );

	assert_receipts_are_equal ('Third Cart Test', $expected, $cart3->receipt() ); // echo $cart3;




/*
	function to display assertions 
*/

	function assert_receipts_are_equal ( $name, $expected = null, $value = null )
	{
		echo "$name >> ", ( $expected === $value ? "passed" : "failed" ), "\n";
	}