#!/usr/local/munki/munki-python
# Author tuxudo

import subprocess
import os
import plistlib
import sys
import platform
import json

sys.path.insert(0, '/usr/local/munki')
sys.path.insert(0, '/usr/local/munkireport')

from munkilib import FoundationPlist

def get_thunderbolt_info():
    '''Uses system profiler to get Thunderbolt info for this machine.'''
    cmd = ['/usr/sbin/system_profiler', 'SPThunderboltDataType', '-xml']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        try:
            plist = plistlib.readPlistFromString(output)
        except AttributeError as e:
            plist = plistlib.loads(output)
        # system_profiler xml is an array
        sp_dict = plist[0]
        items = sp_dict['_items']
        return items
    except Exception:
        return {}

def flatten_thunderbolt_info(array, localization):
    '''Un-nest Thunderbolt devices, return array with objects with relevant keys'''
    out = []
    for obj in array:
        # Return nothing if machine doesn't have Thunderbolt
        if 'Thunderbolt' in obj and obj['Thunderbolt'] == "no_hardware":
            return {}

        device = {}
        for item in obj:
            # Don't process the bus itself
            if item == '_name' and "thunderbolt_bus" in obj[item]:
                continue  
            elif item == '_items':
                out = out + flatten_thunderbolt_info(obj['_items'], localization)
            elif item == 'receptacle_upstream_ambiguous_tag' and 'current_speed_key' in obj[item]:
                device['current_speed'] = obj[item]['current_speed_key']
            elif item == 'services_title':
                try:
                    device['name'] = localization[obj[item][0]['_name']].strip()
                except Exception:
                    device['name'] = obj[item][0]['_name']
                # Save the whole device as a JSON for later processing
                device['device_json'] = json.dumps(obj)
            elif item == '_name' and 'services_title' not in obj:
                device['name'] = obj[item]
                # Save the whole device as a JSON for later processing
                device['device_json'] = json.dumps(obj)
            elif item == 'vendor_name_key':
                device['vendor'] = obj[item]
            elif item == 'aapl_serial_number_key' or item == 'serial_number' or item == 'device_serial_number':
                device['device_serial_number'] = obj[item]

        # Only append device if it has a name
        if 'name' in device:
            out.append(device)
            
    return out

def main():
    """Main"""

    # Read in English localizations from SystemProfiler
    if os.path.isfile('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/en.lproj/Localizable.strings'):
        localization = FoundationPlist.readPlist('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/en.lproj/Localizable.strings')
    elif os.path.isfile('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/English.lproj/Localizable.strings'):
        localization = FoundationPlist.readPlist('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/English.lproj/Localizable.strings')
    else:
        localization = {}

    # Get results
    result = dict()
    info = get_thunderbolt_info()
    result = flatten_thunderbolt_info(info, localization)

    # Write thunderbolt results to cache
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'thunderbolt.plist')
    try:
        plistlib.writePlist(result, output_plist)
    except:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)

if __name__ == "__main__":
    main()
