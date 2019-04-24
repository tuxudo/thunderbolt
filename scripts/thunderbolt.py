#!/usr/bin/python
# Author tuxudo

import subprocess
import os
import plistlib
import sys
import platform
import json

sys.path.insert(0, '/usr/local/munki')

from munkilib import FoundationPlist

def get_thunderbolt_info():
    '''Uses system profiler to get Thunderbolt info for this machine.'''
    cmd = ['/usr/sbin/system_profiler', 'SPThunderboltDataType', '-xml']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        plist = plistlib.readPlistFromString(output)
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
            if item == '_name' and obj[item] == "thunderbolt_bus":
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

def getOsVersion():
    """Returns the minor OS version."""
    os_version_tuple = platform.mac_ver()[0].split('.')
    return int(os_version_tuple[1])

def main():
    """Main"""
    # Create cache dir if it does not exist
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    if not os.path.exists(cachedir):
        os.makedirs(cachedir)

    # Skip manual check
    if len(sys.argv) > 1:
        if sys.argv[1] == 'manualcheck':
            print 'Manual check: skipping'
            exit(0)

    # Set the encoding
    reload(sys)
    sys.setdefaultencoding('utf8')

    # Read in English localizations from SystemProfiler
    if os.path.isfile('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/en.lproj/Localizable.strings'):
        localization = FoundationPlist.readPlist('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/en.lproj/Localizable.strings')
    elif os.path.isfile('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/English.lproj/Localizable.strings'):
        localization = FoundationPlist.readPlist('/System/Library/SystemProfiler/SPThunderboltReporter.spreporter/Contents/Resources/English.lproj/Localizable.strings')
    else:
        print 'No SystemProfiler localization file found. Exiting'
        exit(1)

    # Get results
    result = dict()
    info = get_thunderbolt_info()
    result = flatten_thunderbolt_info(info, localization)

    # Write thunderbolt results to cache
    output_plist = os.path.join(cachedir, 'thunderbolt.plist')
    plistlib.writePlist(result, output_plist)
#    print plistlib.writePlistToString(result)

if __name__ == "__main__":
    main()
