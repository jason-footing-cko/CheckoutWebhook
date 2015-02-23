//
//  CheckoutError.h
//  Checkout
//
//  Created by Zhe Wang on 22/1/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import <Foundation/Foundation.h>

/**
 *  All Checkout iOS errors will be under this domain.
 */
FOUNDATION_EXPORT NSString *const CheckoutDomain;

typedef enum CheckoutErrorCode {

    CKOValidationError = 400,     // General-purpose API error (should be rare).
    CKOTransactionError = 402,    // Payload is valid but not configured at the merchant channel
    CKOSoftDecline = 20,
    CKOHardDecline = 30,
    CKORiskDecline = 40,
    CKOCustomDecline = 60,
    CKOAPIError = 500,
    CKOCardError = 400
} CheckoutErrorCode;

#pragma mark userInfo keys

// A developer-friendly error message that explains what went wrong. You probably
// shouldn't show this to your users, but might want to use it yourself.
FOUNDATION_EXPORT NSString *const CKOErrorMessageKey;

// What went wrong with your CKOCard (e.g., CKOInvalidCVC. See below for full list).
FOUNDATION_EXPORT NSString *const CKOCardErrorCodeKey;

// Which parameter on the CKOCard had an error (e.g., "cvc"). Useful for marking up the
// right UI element.
FOUNDATION_EXPORT NSString *const CKOErrorParameterKey;

#pragma mark CKOCardErrorCodeKeys

// (Usually determined locally:)
FOUNDATION_EXPORT NSString *const CKOInvalidNumber;
FOUNDATION_EXPORT NSString *const CKOCardInvalidExpiryDate;
FOUNDATION_EXPORT NSString *const CKOInvalidCVV;


// (Usually sent from the server:)
FOUNDATION_EXPORT NSString *const CKOIncorrectNumber;
FOUNDATION_EXPORT NSString *const CKOExpiredCard;
FOUNDATION_EXPORT NSString *const CKOCardDeclined;
FOUNDATION_EXPORT NSString *const CKOProcessingError;
FOUNDATION_EXPORT NSString *const CKOIncorrectCVC;

#pragma mark Strings

#define CKOCardErrorInvalidNumberUserMessage NSLocalizedString(@"Your card's number is invalid", @"Error when the card number is not valid")
#define CKOCardErrorInvalidCVVUserMessage NSLocalizedString(@"Your card's security code is invalid", @"Error when the card's CVC is not valid")
#define CKOCardInvalidExpiryDateUserMessage                                                                                                                 \
NSLocalizedString(@"Your card's expiration date is invalid", @"Error when the card's expiration date is not valid")

#define CKOCardErrorExpiredCardUserMessage NSLocalizedString(@"Your card has expired", @"Error when the card has already expired")
#define CKOCardErrorDeclinedUserMessage NSLocalizedString(@"Your card was declined", @"Error when the card was declined by the gateway / credit card networks")

#define CKOUnexpectedError                                                                                                                                     \
NSLocalizedString(@"There was an unexpected error -- try again in a few seconds", @"Unexpected error, such as a 500 from Checkout or a JSON parse error")

#define CKORequestValidationError                                                                                                                                     \
NSLocalizedString(@"There was an error in the JSON Payload that makes the request invalid", @"Please check the payload again before retry")

#define CKOTransactionProcessingError                                                                                                                                     \
NSLocalizedString(@"There was an error when processing your request", @"Please check the channel configuration")

#define CKOCardErrorProcessingErrorUserMessage                                                                                                                 \
NSLocalizedString(@"There was an error processing your card -- try again in a few seconds", @"Error when there is a problem processing the credit card")
