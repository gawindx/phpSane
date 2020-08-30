phpSANE is a web-based frontend for SANE written in HTML/PHP so you can scan with your web-browser. It also supports OCR.  
It's forked from [SourceForge phpSane](https://sourceforge.net/projects/phpsane/)

[![Screen Shot 1](https://github.com/gawindx/phpSane/blob/master/images/phpSane_Screenshot_1.png)](https://github.com/gawindx/phpSane)
[![Screen Shot 2](https://github.com/gawindx/phpSane/blob/master/images/phpSane_Screenshot_2.png)](https://github.com/gawindx/phpSane)

## Installing phpSANE on FreeBSD

### Prerequisites
`pkg_add -r sane-backends`  
`pkg_add -r sane-frontends`  
`pkg_add -r git*`

### Testing  
The following programs must succeed, because phpSANE is based on them.

`sane-find-scanner -q`  
`scanimage -L`  
`su -m www -c 'scanimage --test'`  

### Obtain the source code
#### Use git
`cd /var/www`  
`git clone https://github.com/gawindx/phpSane.git`

#### Or download the latest version
`mkdir /var/www/phpsane`  
`cd /var/www`  
`fetch https://github.com/gawindx/phpSane/archive/master.zip` 
`unzip master.zip`

### Set permissions
`chown -R root:www phpsane/tmp phpsane/output`  
`chmod 775 phpsane/tmp phpsane/output`

## Apache configuration
`  <Location /phpsane>`  
`    DirectoryIndex index.php`  
`    Require group Admins Users Faktura`  
`   </Location>`

## For Fedora User
on a fresh install, you need to install "netpbm-progs" :

`dnf install netpbm-progs`

## OPTIONAL: 
### SELinux

If SELinux is activated and Enforced, you need to apply this custom rule :  
`semanage fcontext -a -t httpd_sys_content_t "/var/www/phpsane(/.*)?"`  
`semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/phpsane/scanners(/.*)?"`  
`semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/phpsane/tmp(/.*)?"`  
`semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/phpsane/output(/.*)?"`  
`restorecon -R -v /var/www/phpsane`  

### devfs.rules for jails
Of course you'll ned to adjust the device name to match the USB port your scanner uses.

`pw groupadd -n scan`  
`pw groupmod scan -m www`  
`pw groupadd -n printscan`  
`pw groupmod printscan -m www`  
`service apache22 restart`

#### /etc/devfs.rules

`[devfsrules_jail_unhide_usb_printer_and_scanner=30]`  
`add include $devfsrules_hide_all`  
`add include $devfsrules_unhide_basic`  
`add include $devfsrules_unhide_login`  
`add path 'ulpt*' mode 0660 group printscan unhide`  
`add path 'unlpt*' mode 0660 group printscan unhide`  
`add path 'ugen2.8' mode 0660 group printscan unhide  # Scanner (ugen2.8 is a symlink to usb/2.8.0)`  
`add path usb unhide`  
`add path usbctl unhide`  
`add path 'usb/2.8.0' mode 0660 group printscan unhide`  

`[devfsrules_jail_unhide_usb_scanner_only=30]`  
`add include $devfsrules_hide_all`  
`add include $devfsrules_unhide_basic`  
`add include $devfsrules_unhide_login`  
`add path 'ugen2.8' mode 0660 group scan unhide  # Scanner`  
`add path usb unhide`  
`add path usbctl unhide`  
`add path 'usb/2.8.0' mode 0660 group scan unhide`  
