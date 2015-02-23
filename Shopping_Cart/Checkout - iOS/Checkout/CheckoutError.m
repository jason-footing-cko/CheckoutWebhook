//
//  CheckoutError.m
//  Checkout
//
//  Created by Zhe Wang on 9/2/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//


#import "CheckoutError.h"

NSString *const CheckoutDomain = @"com.checkout.lib";
NSString *const CKOCardErrorCodeKey = @"com.checkout.lib:CardErrorCodeKey";
NSString *const CKOErrorMessageKey = @"com.checkout.lib:ErrorMessageKey";
NSString *const CKOErrorParameterKey = @"com.checkout.lib:ErrorParameterKey";
NSString *const CKOInvalidNumber = @"com.checkout.lib:InvalidNumber";
NSString *const CKOCardInvalidExpiryDate = @"com.checkout.lib:CardInvalidExpiryDate";
NSString *const CKOInvalidCVV = @"com.checkout.lib:InvalidCVV";
NSString *const CKOIncorrectNumber = @"com.checkout.lib:IncorrectNumber";
NSString *const CKOExpiredCard = @"com.checkout.lib:ExpiredCard";
NSString *const CKOCardDeclined = @"com.checkout.lib:CardDeclined";
NSString *const CKOProcessingError = @"com.checkout.lib:ProcessingError";
NSString *const CKOIncorrectCVC = @"com.checkout.lib:IncorrectCVC";