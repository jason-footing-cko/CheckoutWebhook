//
//  CKOCard.h
//  Checkout
//
//  Created by Zhe Wang on 22/1/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import <Foundation/Foundation.h>

typedef NS_ENUM(NSInteger, CKOCardPaymentMethod){
    CKOCardPaymentMethodDebit,
    CKOCardPaymentMethodCredit,
    CKOCardPaymentMethodPrepaid,
    CKOCardPaymentMethodOther,
};


/**
 *  Representation of a user's credit card details.
 
 *  Collect these information from user's entry and assemble these information 
 
 *  to create Stripe Token with them using CKOAPIClient.
 
 *  API References: http://dev.checkout.com/ref/apiwebsite/#cards-object
 
 */

@interface CKOCard : NSObject

/**
 
 *  The card's number. This will be nil for cards retrieved from the Checkout API
 
 */

@property(nonatomic, copy) NSString *number;

/**
 
 *  The card's last 4 digits. This will be present on cards retrieved from the Checkout API
 
 */

@property(nonatomic, readonly) NSString *last4;

/**
 
 *  The card's expiration month
 
 */

@property(nonatomic)  NSUInteger expiryMonth;

/**
 
 *  The card's expiration year
 
 */

@property(nonatomic)  NSUInteger expiryYear;

/**
 
 *  The card's CVV number. This will be nil for cards retrieved from the Checkout API
 
 */

@property(nonatomic, copy) NSString *cvv;


/**
 
 *  The cardholder's name
 
 */

@property(nonatomic, copy) NSString *name;

/**
 
 *  The card's billing address
 
 */

@property(nonatomic, copy) NSString *addressLine1;
@property(nonatomic, copy) NSString *addressLine2;
@property(nonatomic, copy) NSString *addressPostcode;
@property(nonatomic, copy) NSString *country;
@property(nonatomic, copy) NSString *city;
@property(nonatomic, copy) NSString *state;
@property(nonatomic, copy) NSString *phone;

/**
 
 *  The Checkout Card ID
 
 */
@property(nonatomic, readonly) NSString *cardID;

/**
 
 *  The Payment Method - Creditcard, Debit card, prepaid card or others
 
 */
@property(nonatomic,readonly) CKOCardPaymentMethod paymentMethod;


/**
 
 *  The Card's fingerprint
 
 */
@property(nonatomic, readonly) NSString *fingerprint;

/**
 
 *  Validation methods:
 *  Described as https://developer.apple.com/library/mac/documentation/Cocoa/Conceptual/KeyValueCoding/Articles/Validation.html#//apple_ref/doc/uid/20002173-CJBDBHCB
 
 */

// determine the card validality locally before proceed

- (BOOL)validateNumber:(id *)ioValue error:(NSError **)outError;
- (BOOL)validateCVV:(id *)ioValue error:(NSError **)outError;
- (BOOL)validateMonth:(id *)ioValue error:(NSError **)outError;
- (BOOL)validateYear:(id *)ioValue error:(NSError **)outError;

/**
 
 * Validate fully populated card to check for all errors
 
 *  @param outError a pointer to an NSError that after calling this method, will be populated with an error if the card is not validate
 
 */

- (BOOL)validateCard:(NSError **)outError;



@end

@interface CKOCard (PrivateMethods)
- (instancetype)initWithAttributeDictionary:(NSDictionary *)attributeDictionary;
@end