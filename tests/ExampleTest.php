<?php

it('can write csv', function () {

    $w = new \IvanoMatteo\CsvReadWrite\CsvWriter(__DIR__."/tmp.csv");

    $w->write([
        ['aaa','bbb','ccc'],
        ['aaa','bbb','ccc'],
        ['aaa','bbb',"ccc \n\n xxx"],
        ['aaa','bbb','ccc'],
        ['aaa','bbb','ccc'],
    ],['a','b','c']);

    expect(true)->toBeTrue();
});


it('can read csv', function () {

    $file = __DIR__."/tmp.csv";
    $r = new \IvanoMatteo\CsvReadWrite\CsvReader($file);

    foreach($r->iterator() as $row){
        expect($row)->toBeArray()->toHaveCount(3)->toHaveKeys(['a','b','c']);
    }

    unlink($file);

})->depends('it can write csv');


