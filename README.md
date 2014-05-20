# CIRCUS CS (Clinical Server)

A web-based CAD (computer-assisted diagnosis) execution platform.

[Project Website](http://www.ut-radiology.umin.jp/ical/CIRCUS/index.html)

## Installation

You can use all-in-one installer (*.msi package) which installs Apache, PHP and PostgreSQL along with CIRCUS CS itself. To avoid compatibility issues, use this option if possible.

If you prefer manual installation, read the following steps:

- Install the latest version of Apache 2.x and PostgreSQL 9.x
- Install PHP 5 as an Apache module. Version 5.3 is the current supported version.
- Download and extract the CIRCUS CS zip archive.
- (Optional and developmental purpose only) Replace `web_ui` directory with this git repository.
- Configure Apache to host `web_ui/pub` directory as an arbitrary URL.
- Register DICOM Storage Server and Plugin Job Manager as windows services.
- Prepare cache directory.
- ... to be continued.


## Documentation

Online documentation (in Japanese) is available in CIRCUS CS project site.

## License

Modified BSD License.