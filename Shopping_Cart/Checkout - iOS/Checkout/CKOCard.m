//
//  CKOCard.m
//  Checkout
//
//  Created by Zhe Wang on 22/1/15.
//  Copyright (c) 2015 Checkout Ltd. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "CKOCard.h"
#import "CheckoutError.h"

@interface CKOCard()

@property(nonatomic, readwrite) NSString *cardID;
@property(nonatomic, readwrite) NSString *last4;
@property(nonatomic, readwrite) CKOCardPaymentMethod paymentMethod;
@property(nonatomic, readwrite) NSString *fingerprint;

@end


@implementation CKOCard

- (instancetype)init{
    self = [super init];
    if(self){
        _paymentMethod = CKOCardPaymentMethodOther;
    }
    
    return self;
}

- (NSString *)last4{
    if(_last4){
        return _last4;
    }else if (self.number && self.number.length >=4){
        return [self.number substringFromIndex:(self.number.length-4)];
    }else{
        return nil;
    }
}

- (BOOL)validateNumber:(id *)ioValue error:(NSError **)outError{
    if(*ioValue == nil){
        return [CKOCard handleValidationErrorforParameter:@"number" error:outError];
    }
    
    NSString *ioValueString = (NSString *)*ioValue;
    NSRegularExpression *regex = [NSRegularExpression regularExpressionWithPattern:@"[\\s+|-]" options:NSRegularExpressionCaseInsensitive error:NULL];
    NSString *rawNumber = [regex stringByReplacingMatchesInString:ioValueString options:0 range:NSMakeRange(0, [ioValueString length]) withTemplate:@""];
    
    if(rawNumber == nil || rawNumber.length <10 || rawNumber.length > 19 || ![CKOCard isLuhnValidNumber:rawNumber]){
        return [CKOCard handleValidationErrorforParameter:@"number" error:outError];
    }
    return YES;
    
    
}

- (BOOL)validateCVV:(id *)ioValue error:(NSError **)outError{
    if(*ioValue == nil){
        return [CKOCard handleValidationErrorforParameter:@"CVV" error:outError];
    }
    NSString *cvv = [(NSString *)*ioValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    BOOL validCVVLength = ({
        BOOL valid;
        valid = (cvv.length == 3 || cvv.length == 4);
        valid;
    });
    
    if ([CKOCard isNumericOnlyString:cvv] || !validCVVLength){
        return [CKOCard handleValidationErrorforParameter:@"cvv" error:outError];
    }
    
    return YES;
    
    
}

-(BOOL)validateMonth:(id *)ioValue error:(NSError **)outError{
    if(*ioValue == nil){
        return [CKOCard handleValidationErrorforParameter:@"expMonth" error:outError];
    }
    
    NSString *expMonth = [(NSString *)*ioValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
    NSInteger expMonthInt = [expMonth integerValue];
    
    if(![CKOCard isNumericOnlyString:expMonth] || expMonthInt > 12 || expMonthInt < 1){
        return [CKOCard handleValidationErrorforParameter:@"expMonth" error:outError];
    }
    
    else if ([self expiryYear] && [CKOCard isExpiredMonth: expMonthInt andYear:[self expiryYear] atDate:[NSDate date]]){
        NSUInteger currentYear = [CKOCard currentYear];
        if(currentYear > [self expiryYear]){
            return [CKOCard handleValidationErrorforParameter:@"expYear" error:outError];
        }else{
            return [CKOCard handleValidationErrorforParameter:@"expMonth" error:outError];
        }
    }
    
    return YES;
    
}

-(BOOL)validateYear:(id *)ioValue error:(NSError **)outError{
    if(*ioValue == nil){
        return [CKOCard handleValidationErrorforParameter:@"expYear" error:outError];
    }
    
    NSString *expYear = [(NSString *)*ioValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSInteger expYearInt = [expYear integerValue];
    if(![CKOCard isNumericOnlyString:expYear] || expYearInt < [CKOCard currentYear]){
        return [CKOCard handleValidationErrorforParameter:@"expYear" error:outError];
    } else if ([self expiryMonth] && [CKOCard isExpiredMonth:[self expiryMonth] andYear:expYearInt atDate:[NSDate date]]){
        return [CKOCard handleValidationErrorforParameter:@"expMonth" error:outError];
    }
    
    return YES;
    
}

-(BOOL)validateCard:(NSError **)outError{
    NSString *numberRef = [self number];
    NSString *expMonthRef = [NSString stringWithFormat:@"%lu", (unsigned long)[self expiryMonth]];
    NSString *expYearRef = [NSString stringWithFormat:@"%lu", (unsigned long)[self expiryYear]];
    NSString *cvvRef = [self cvv];
    
    return [self validateNumber:&numberRef error:outError]&&
    (cvvRef == nil || [self validateCVV:&cvvRef error:outError])&&
    [self validateMonth:&expMonthRef error:outError]&&
    [self validateYear:&expYearRef error:outError];
}

#pragma mark Private Helpers

+ (BOOL)handleValidationErrorforParameter:(NSString *)parameter error:(NSError **) outError{
    if (outError != nil) {
        if([parameter isEqualToString:@"number"]){
            *outError = [self createErrorwithMessage: CKOCardErrorInvalidNumberUserMessage
                                           parameter:parameter
                                       cardErrorCode:CKOInvalidNumber
                                        devErrorMessage:@"Card number must be between 10 and 19 digits long and luhn valid"];
            
        } else if([parameter isEqualToString:@"cvv"]){
            *outError = [self createErrorwithMessage:CKOCardErrorInvalidCVVUserMessage
                                           parameter:parameter
                                       cardErrorCode:CKOInvalidCVV
                                     devErrorMessage:@"Card CVV must be numeric, 3 or 4 digits"];
        } else if([parameter isEqualToString:@"expMonth"] || [parameter isEqualToString:@"expYear"]){
            *outError = [self createErrorwithMessage:CKOCardInvalidExpiryDateUserMessage
                                           parameter:parameter
                                       cardErrorCode:CKOCardInvalidExpiryDate
                                     devErrorMessage:@"Card Expiry Date must be valid, month must be less than 13, year must be this year or a year in the future"];
        } else{
            *outError = [[NSError alloc] initWithDomain:CheckoutDomain
                                                   code:CKOAPIError
                                               userInfo:@{
                                                          NSLocalizedDescriptionKey: CKOUnexpectedError,
                                                          CKOErrorMessageKey: @"There was an error within the Checkout client library when trying to generate the "
                                                          @"proper validation error."
                                                          }];
            
        }

        
    }
    return NO;
    
}

+ (BOOL)isLuhnValidNumber:(NSString *)number{
    BOOL isOdd = true;
    NSInteger sum = 0;
    
    NSNumberFormatter *numberFormatter = [[NSNumberFormatter alloc] init];
    for (NSInteger index = [number length] - 1; index >=0; index --) {
        NSString *digit = [number substringWithRange:NSMakeRange(index, 1)];
        NSNumber *digitNumber = [numberFormatter numberFromString:digit];
        
        if (digitNumber == nil) {
            return NO;
        }
        
        NSInteger digitInteger = [digitNumber intValue];
        isOdd = !isOdd;
        
        if (isOdd){
            digitInteger *=2;
        }
        
        if(digitInteger >9){
            digitInteger -= 9;
        }
        
        sum += digitInteger;
    }
    return sum%10 == 0;
}

+(BOOL)isNumericOnlyString:(NSString *)number{
    NSCharacterSet *numericOnly = [NSCharacterSet decimalDigitCharacterSet];
    NSCharacterSet *numberSet = [NSCharacterSet characterSetWithCharactersInString:number];
    
    return [numericOnly isSupersetOfSet:numberSet];
    
}

+ (NSCalendar *)gregorianCalendar{
    #pragma clang diagnostic push
    #pragma clang diagnostic ignored "-Wdeprecated"
    #pragma clang diagnostic ignored "-Wunreachable-code"
    NSString *identifier = (&NSCalendarIdentifierGregorian != nil) ? NSCalendarIdentifierGregorian : NSGregorianCalendar;
    #pragma clang diagnostic pop
    return [[NSCalendar alloc] initWithCalendarIdentifier:identifier];
}

+ (BOOL)isExpiredMonth:(NSInteger)month andYear:(NSInteger)year atDate:(NSDate *)date{
    NSDateComponents *components = [[NSDateComponents alloc] init];
    [components setYear:year];
    [components setMonth:month+1];
    [components setDay:1];
    
    NSDate *expiryDate = [[self gregorianCalendar] dateFromComponents:components];
    
    return ([expiryDate compare:date] == NSOrderedAscending);
    
}

+ (NSInteger)currentYear{
    NSDateComponents *components = [[self gregorianCalendar] components:NSCalendarUnitYear fromDate:[NSDate date]];
    return [components year];
}

+ (NSError *)createErrorwithMessage:(NSString *)userMessage
                          parameter:(NSString *)parameter
                      cardErrorCode:(NSString *)cardErrorCode
                    devErrorMessage:(NSString *)devMessage{
    return [[NSError alloc] initWithDomain:CheckoutDomain
                                      code:CKOCardError
                                  userInfo:@{
                                             NSLocalizedDescriptionKey:userMessage,
                                             CKOErrorParameterKey:parameter,
                                             CKOCardErrorCodeKey: cardErrorCode,
                                             CKOErrorMessageKey: devMessage
                                             }];
    
}
@end


@implementation CKOCard(PrivateMethods)

- (instancetype)initWithAttributeDictionary:(NSDictionary *)attributeDictionary {
    self = [self init];
    
    NSMutableDictionary *dict = [NSMutableDictionary dictionary];
    
    [attributeDictionary enumerateKeysAndObjectsUsingBlock:^(id key, id obj, __unused BOOL *stop) {
        if (obj != [NSNull null]) {
            dict[key] = obj;
        }
    }];
    
    if (self) {
        _cardID = dict[@"id"];
        _number = dict[@"number"];
        _cvv = dict[@"cvc"];
        _name = dict[@"name"];
        _last4 = dict[@"last4"];

        NSString *paymentMethod = dict[@"paymentMethod"];
        if ([paymentMethod.lowercaseString isEqualToString:@"CreditCard"]) {
            _paymentMethod = CKOCardPaymentMethodCredit;
        } else if ([paymentMethod.lowercaseString isEqualToString:@"DebitCard"]) {
            _paymentMethod = CKOCardPaymentMethodDebit;
        } else if ([paymentMethod.lowercaseString isEqualToString:@"Prepaid"]) {
            _paymentMethod = CKOCardPaymentMethodPrepaid;
        } else {
            _paymentMethod = CKOCardPaymentMethodOther;
        }
        
        _fingerprint = dict[@"fingerprint"];
        _country = dict[@"country"];
        // Support both camelCase and snake_case keys
        _expiryMonth = [(dict[@"expiryMonth"] ?: dict[@"expiry_Month"])intValue];
        _expiryYear = [(dict[@"expiryYear"] ?: dict[@"expry_Year"])intValue];
        
        _addressLine1 = dict[@"billingDetails"][@"address_line1"] ?: dict[@"billingDetails"][@"addressLine1"];
        _addressLine2 = dict[@"billingDetails"][@"address_line2"] ?: dict[@"billingDetails"][@"addressLine2"];
        _addressPostcode = dict[@"billingDetails"][@"address_Postalcode"] ?: dict[@"billingDetails"][@"addressPostalcode"];
        _country = dict[@"billingDetails"][@"country"] ?: dict[@"billingDetails"][@"country"];
        _city = dict[@"billingDetails"][@"city"] ?: dict[@"billingDetails"][@"city"];
        _state = dict[@"billingDetails"][@"state"] ?: dict[@"billingDetails"][@"state"];
        _phone = dict[@"billingDetails"][@"phone"] ?: dict[@"billingDetails"][@"phone"];
    }
    
    return self;
}

@end