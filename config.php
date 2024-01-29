<?php

return [
  /*
  |===============================================
  | thunderbolt
  |===============================================
  |
  | By default, thunderbolt does not keep historical
  | devices that have been connected in the past.
  | To enable, set `thunderbolt_historical` to
  | true.
  |
  */

  'thunderbolt_historical' => env('THUNDERBOLT_HISTORICAL', false),
];
