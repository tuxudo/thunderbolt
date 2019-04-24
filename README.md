Thunderbolt module
==============

Provides the status of Thunderbolt devices.

Data can be viewed under the Thunderbolt Devices tab on the client details page or using the Thunderbolt list view.

Thanks to eholtam and foigus for providing data to test with

Table Schema
---
* name - varchar(255) - name of the Thunderbolt device
* device_serial_number - varchar(255) - serial number of Thunderbolt device
* vendor - varchar(255) - device vendor
* current_speed - varchar(255) - current link speed
* device_json - text - JSON string of device

