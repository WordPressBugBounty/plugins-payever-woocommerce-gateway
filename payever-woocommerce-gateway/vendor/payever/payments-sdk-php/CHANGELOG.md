# Changelog

## [2.5.0]
### Added
- Added business type property to AbstractPaymentOptionEntity
- Added claim_upload action

## [2.4.2]
### Changed
- Made allianz_trade_b2b_bnpl as hidden method arter failure payment

## [2.4.1]
### Changed
- Company Search result fixes

## [2.4.0]
### Added
- Implemented Settle payment action

## [2.3.0]
### Added
- Add shop setting endpoint

### Changed
- Updated setters in `CreatePaymentV3Request` class
- Improved parameters handling using `DataHelper`
- Fixed possible problems with `DataTime` objects and `date_create()` functions
- Fixed for `CustomerEntity` class

## [2.2.0]
### Added
- Added options for payment variants

## [2.1.0]
### Added
- Added `isB2bMethod` flag
- Added `/api/b2b/search/credit`

## [2.0.1]
### Added
- Added `getDeliveryFee()` and `setDeliveryFee()` methods

## [2.0.0]
### Changed
- Removed `psr/log` dependency

## [1.5.3]
### Removed
- Google Pay for desktop
 
## [1.5.2]
### Added
- Implemented Google Pay, Apple Pay filters

## [1.5.1]
### Added
- Implemented `getExtraData` and `setExtraData` for cart items

## [1.5.0]
### Added
- Unique identifier to the payment actions

## [1.4.0]
### Added
- Implemented 'reference' to the payment actions
 
## [1.1.0]
### Added
- Implemented api v3

## [1.0.0]
### Added
- initial version;
