<?php

class PaginationTest extends \plainview\sdk_mcc\tests\TestCase
{
	public function test_large_width()
	{
		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->page( 50 )
			->pages( 100 )
			->width( 5 )
			->render();

		$pages_to_render = $p->get_pages_to_render();
		$correct_pages = [
			1,
			2,
			3,
			4,
			5,
			6,
			'...',
			45,
			46,
			47,
			48,
			49,
			50,
			51,
			52,
			53,
			54,
			55,
			'...',
			95,
			96,
			97,
			98,
			99,
			100
		];
		$this->assertEquals( $correct_pages, $pages_to_render );
	}

	public function test_small_width()
	{
		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->page( 50 )
			->pages( 100 )
			->width( 1 )
			->render();

		$pages_to_render = $p->get_pages_to_render();
		$correct_pages = [
			1,
			2,
			'...',
			49,
			50,
			51,
			'...',
			99,
			100
		];
		$this->assertEquals( $correct_pages, $pages_to_render );
	}

	public function test_small()
	{
		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->page( 5 )
			->pages( 10 )
			->width( 5 )
			->render();

		$pages_to_render = $p->get_pages_to_render();
		$correct_pages = [
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10
		];
		$this->assertEquals( $correct_pages, $pages_to_render );
	}

	public function test_page_limiter()
	{
		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->page( 55 )
			->pages( 10 )
			->width( 5 )
			->render();

		$this->assertEquals( 10, $p->page );

		$pages_to_render = $p->get_pages_to_render();
		$correct_pages = [
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10
		];
		$this->assertEquals( $correct_pages, $pages_to_render );
	}

	public function test_per_page()
	{
		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->count( 20 )
			->per_page( 10 )
			->render();
		$this->assertEquals( 2, $p->pages );

		$p = new \plainview\sdk_mcc\pagination\pagination;
		$p->count( 50 )
			->per_page( 49 )
			->render();
		$this->assertEquals( 2, $p->pages );
	}
}
