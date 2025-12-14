POST

# **F002-Obtain plans**

Plans

The sales channel can obtain the list of plans it can sell, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F002- Obtain Plans |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └salesMethod | String | Y | Sales modes: 1- retailing; 2-OTA; 3- wholesale; 4- commission; 5- distirbution; 6- others |
| └skuId | String | N | Commodity ID |
| └networkOperatorScope | String | N | Network operator scope: 1-1 level 2- all (not passed by default 1 level network operators). |
| └language | String | N | Language: 1- Chinese; 2- English (if not uploaded, 1 by default) |
| └countryCode | String | N | Country code |

### Request Example

Copy  
{  
  "tradeType": "F002",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "salesMethod": "1",  
    "language": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └skuId | String | Y | Commodity ID |
| └name | String | Y | Commodity name |
| └type | String | Y | Commodity type: 110- self-selected data plan; 111- fixed data plan; 210- single-time card; 211- multi-time card; 212- hard card; 220- MIFI sales; 221- MIFI leasing; 230-eSIM; 311- hard card \+ flow; 3101- single-time card \+ self-selected data plan; 3102- single-time card \+ fixed data plan; 3103- multi-time card \+ self-selected data plan; 3104- multi-time card \+ self-selected data plan; 3105- eSIM \+self-selected data plan; 3106- eSIM \+ fixed data plan; 3201- MIFI sales \+ self-selected data plan; 3202- MIFI sales \+ fixed data plan; 3211- MIFI leasing \+ self-selected data plan; 3212- MIFI sales \+ fixed data plan |
| └days | String | N | Number of days (day) |
| └capacity | String | N | Data data plan size (KB) |
| └highFlowSize | String | N | High flow size (KB/day) |
| └limitFlowSpeed | String | N | Limit flow speed (kbps) |
| └hotspotSupport | String | N | Hotspot support: 0- support; 1- not support |
| └country | Array | N | Country |
| └mcc | String | Y | Country code |
| └name | String | Y | Country name |
| └apn | String | Y | Apn information |
| └apnUsername | String | N | Apn username |
| └apnPassword | String | N | Apn password |
| └apnType | String | N | Apn set type: 0-No setting required 1-No setting required |
| └authenticationType | String | N | Authentication type |
| └apnTypeDesc | String | N | Apn type |
| └highSpeedTime | String | N | High Speed Data Reset Time |
| └operatorInfo | Array | Y | Operator infor |
| └operator | String | Y | Network operator |
| └network | String | Y | Network standard |
| └priority | String | Y | priority |
| └productId | String | N | Product id |
| └productName | String | N | Product name |
| └apn | String | N | Apn information |
| └operatorInfo | String | N | Network operator and network type information |
| └planType | String | N | Plan type:0-type the total sku 1-type in a single day sku |
| └validityPeroid | String | N | Validity peroid |
| └accelerationSupport | String | N | Acceleration support:0-support 1-not support |
| └desc | String | Y | Commodity description |
| └pointContactType | String | N | Daily cut-off point type: 0-24 hour system 1-daily settlement system |
| └timeZone | String | N | Operator Time Zone |
| └pointContactHours | String | N | Daily cut-off time |
| └usageCount | String | N | Times of carrier can be used：1-One time use 2-Multiple use |
| └estimatedUseTimeFlag | String | N | EstimatedUseTime filling flag: 1- Required 2- No need to fill in |
| └estimatedUseTimeGapHours | String | N | EstimatedUseTime handle gap hours |
| └applyToDevice | String | N | Matching Carrier |
| └applyToDeviceType | Array | N | Applicable Device Type |
| └rechargeableProduct | String | N | Rechargeable product(0-no 1-indicates that the product is a rechargeable eSIM product.) |
| └rechargeableProductSeriesId | String | N | Rechargeable product series id |
| └rechargeableProductSeriesName | String | N | Rechargeable prodcut series name |
| └provider | String | N | Provider |
| └refundPolicy | String | N | Refund Policy |
| └speedLimitRule | String | N | Throttling Policy |
| └carrierValidityPeroid | String | N | Default validity peroid of the carrier |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "skuId": "1090",  
      "name": "Hong Kong-4G-1 day-200M",  
      "type": "110",  
      "days": "1",  
      "capacity": "",  
      "highFlowSize": "20000",  
      "limitFlowSpeed": "128",  
      "hotspotSupport": "1",  
      "country": \[  
        {  
          "mcc": "HK",  
          "name": "Hong Kong",  
          "apn": "3gnet",  
          "apnUsername": "",  
          "apnPassword": "",  
          "apnType": "0",  
          "operatorInfo": \[  
            {  
              "operator": "cuhk",  
              "network": "4G",  
              "priority": "1"  
            }  
          \]  
        }  
      \],  
      "apn": "",  
      "operatorInfo": "",  
      "desc": "Hong Kong 1-day data plan, daily high flow of 200MB, flow not limited the speed is reduced by 128kbps"  
    }  
  \]

}

Plans

The sales channel can obtain the list of plans it can sell, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F003- Obtain plans price |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └salesMethod | String | Y | Sales modes: 1- retailing; 2-OTA; 3- wholesale; 4- commission; 5- distirbution; 6- others |

### Request Example

Copy  
{  
  "tradeType": "F003",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "salesMethod": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └skuId | String | Y | Commodity ID |
| └price | Array | Y | doc.f003.response.priceArrayResponse |
| └copies | String | Y | doc.f003.response.copiesResponse |
| └retailPrice | String | Y | doc.f003.response.retailPriceResponse |
| └settlementPrice | String | Y | doc.f003.response.settlementPriceResponse |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "skuId": "1090",  
      "price": \[  
        {  
          "copies": "1",  
          "retailPrice": "5",  
          "settlementPrice": "4"  
        },  
        {  
          "copies": "2",  
          "retailPrice": "9",  
          "settlementPrice": "7"  
        }  
      \]  
    }  
  \]

}

The sales channel can obtain the self-pick-up site information, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F004- Obtain self-pick-up site information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |

### Request Example

Copy  
{  
  "tradeType": "F004",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {}

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └pointId | String | Y | Self-pick-up site ID |
| └address | String | Y | Address |
| └openingHours | String | Y | Opening hours |
| └gpsInfo | String | Y | GPS information |
| └contactWay | String | N | Contact information |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "pointId": "101",  
      "address": " Area B International Departure Layer, Guangzhou Baiyun International Airport",  
      "openingHours": "7\*24 hours",  
      "gpsInfo": "121.8035020000,31.1489150000",  
      "contactWay": "Tel.:18623123020"  
    }  
  \]

}  
POST

# **F005-Obtain logistics company information**

The sales channel obtains logistics company information, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F005- Obtain logistics company information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |

### Request Example

Copy  
{  
  "tradeType": "F005",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {}

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └code | String | Y | Logistics company code |
| └name | String | Y | Logistics company name |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "code": "STO",  
      "name": "STO Express"  
    },  
    {  
      "code": "YTO",  
      "name": "YTO Express"  
    },  
    {  
      "code": "YUNDA",  
      "name": "Yunda Express"  
    }  
  \]

}

POST

# **F006-Create card order**

SIM Card Order

The user creates card order, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F006- Create card order |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └express | Object | N | Express information (choose either express or selfPickup) |
| └userName | String | 是 | Recipient name |
| └userPhone | String | 是 | Recipient's phone |
| └logisticsCompany | String | 是 | Logistics company |
| └feeMethod | String | 否 | Payment method: 1-pay on delivery; 2-paid by shipper (2 by default if not uploaded) |
| └province | String | 是 | Province |
| └city | String | 是 | City |
| └district | String | 是 | District/County |
| └address | String | 是 | Detailed address |
| └expressFee | String | 否 | Express fee |
| └selfPickup | Object | N | Self-pickup information (choose either express or selfPickup) |
| └userName | String | 是 | Recipient name |
| └userPhone | String | 是 | Recipient's phone |
| └pickupPointId | String | 是 | ID of self-pick-up site |
| └totalAmount | String | N | Total order amount |
| └discountAmount | String | N | Discount amount |
| └estimatedUseTime | String | N | Estimated use time |
| └orderCreateTime | String | N | Order create time |
| └comment | String | N | Remarks |
| └subOrderList | Array | Y | Sub-order set |
| └channelSubOrderId | String | Y | Sub-order ID of sales channel |
| └deviceSkuId | String | Y | Card commodity ID |
| └deviceSkuPrice | String | N | Card commodity price |
| └planSkuId | String | N | Plan commodity ID |
| └planSkuPrice | String | N | Plan commodity price |
| └planSkuCopies | String | Y | Number of plan commodity |
| └number | String | Y | Purchase quantity, scope: 1-1000 |
| └discountAmount | String | N | Discount amount |
| └invoiceType | String | N | Invoice type: 0: individual; 1: company |
| └invoiceHead | String | N | Invoice header |
| └invoiceContent | String | N | Invoice content |
| └invoiceComment | String | N | Invoice notes |
| └userId | String | N | Submitter |

### Request Example

Copy  
{  
  "tradeType": "F006",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "channelOrderId": "138788765467",  
    "express": {  
      "userName": "张三",  
      "userPhone": "15801182258",  
      "logisticsCompany": "SF",  
      "feeMethod": "2",  
      "province": "北京市",  
      "city": "北京市",  
      "district": "海淀区",  
      "address": "上地信息产业基地创业路6号",  
      "expressFee": "10"  
    },  
    "totalAmount": "128",  
    "discountAmount": "1",  
    "estimatedUseTime": "2017-12-31",  
    "orderCreateTime": "2017-12-12 12:12:12",  
    "comment": "请发顺丰",  
    "subOrderList": \[  
      {  
        "channelSubOrderId": "2873987483292",  
        "deviceSkuId": "1535444366670209",  
        "planSkuId": "",  
        "planSkuCopies": "1",  
        "number": "1"  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of channel |
| └pickupCode | String | N | Self-pick-up code |
| └subOrderList | Array | Y | Sub-order information |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "pickupCode": "",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291"  
      },  
      {  
        "subOrderId": "13131313133",  
        "channelSubOrderId": "2873987483292"  
      }  
    \]  
  }

}

POST

# **F007-Create top-up order**

SIM Card OrdereSIM Order

The user creates top-up order, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F007- Create top-up order |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └totalAmount | String | N | Total order amount |
| └discountAmount | String | N | Discount amount |
| └estimatedUseTime | String | N | Estimated use time |
| └orderCreateTime | String | N | Order create time |
| └comment | String | N | Remarks |
| └subOrderList | Array | Y | Sub-order set |
| └channelSubOrderId | String | Y | Sub-order ID of sales channel |
| └ICCID | Array | Y | Top-up card number array, no repetition, scope: 1-500 |
| └skuId | String | Y | Plan commodity ID |
| └copies | String | Y | Number of plan commodity |
| └price | String | N | Selling price |
| └discountAmount | String | N | Discount amount |
| └invoiceType | String | N | Invoice type: 0: individual; 1: company |
| └invoiceHead | String | N | Invoice header |
| └invoiceContent | String | N | Invoice content |
| └invoiceComment | String | N | Invoice notes |
| └userId | String | N | Submitter |

### Request Example

Copy  
{  
  "tradeType": "F007",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "channelOrderId": "138788765467",  
    "totalAmount": "128",  
    "orderCreateTime": "2017-12-12 12:12:12",  
    "comment": "",  
    "subOrderList": \[  
      {  
        "channelSubOrderId": "2873987483291",  
        "iccid": \[  
          "89860012017300000001",  
          "89860012017300000002"  
        \],  
        "skuId": "1273",  
        "copies": "5"  
      },  
      {  
        "channelSubOrderId": "2873987483292",  
        "iccid": \[  
          "89860012017300000003"  
        \],  
        "skuId": "1108",  
        "copies": "1"  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of channel |
| └subOrderList | Array | Y | Sub-order information |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291"  
      },  
      {  
        "subOrderId": "13131313133",  
        "channelSubOrderId": "2873987483292"  
      }  
    \]  
  }

}

POST

# **F008-Cancel order**

The sales channel cancels orders created in flow platform, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F008- Cancel order |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └userId | String | N | Submitter |

### Request Example

Copy  
{  
  "tradeType": "F008",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "orderId": "13131313131"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **F009-Modify logistics information**

After the successful creation of card order of the sales channel, the logistics information of the order can be modified before shipment. For this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F009- Modify logistics information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └userName | String | Y | Recipient name |
| └userPhone | String | Y | Recipient's phone number |
| └logisticsCompany | String | Y | Logistics company |
| └province | String | Y | Province |
| └city | String | Y | City |
| └district | String | Y | District/ County |
| └address | String | Y | Address |
| └feeMethod | String | N | doc.f009.params.feeMethod |
| └expressFee | String | N | Express fee |
| └comment | String | N | Remarks |
| └userId | String | N | Submitter |

### Request Example

Copy  
{  
  "tradeType": "F009",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "orderId": "13131313131",  
    "userName": "张三",  
    "userPhone": "15801182258",  
    "logisticsCompany": "SF",  
    "province": "北京市",  
    "city": "北京市",  
    "district": "海淀区",  
    "address": "上地信息产业基地创业路6号",  
    "feeMethod": "2",  
    "expressFee": "10",  
    "comment": "请尽快发货",  
    "userId": "user123"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **F010-Query validity of card**

The sales channel can query the card validity period of the iccid through the interface; for this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F010- Query validity of card |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └ICCID | Array | Y | Card number array to be queried, card number cannot be repeated, scope 1-100 |

### Request Example

Copy  
{  
  "tradeType": "F010",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "iccid": \[  
      "89860012017300000001",  
      "89860012017300000002"  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └ICCID | String | Y | Card ICCID |
| └type | String | Y | type：0- single-time card; 1- multi-time card; 2- hard card; 3- MIFI sales; 4- MIFI leasing;5-eSIM |
| └status | String | Y | Status: 0 \- Open Card 1 \- In Use 2 \- Exhausted 3 \- Failure 4 \- Extension 5 \- Scrap |
| └expirationDate | String | Y | Expiration date of validity of card |
| └postponedMonth | String | Y | Postponed month, One month extension in BC system means an extension of 30 days duration |
| └maxDelayMonth | String | Y | Maximum delay month (-1:no limit) |
| └usageCount | String | Y | Times of carrier can be used:1-one time use 2-multiple use |
| └rechargeableProductSeriesId | String | N | Rechargeable Product Series Id |
| └rechargeableProductSeriesName | String | N | Rechargeable Product Series Name |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "iccid": "89860012017300000001",  
      "type": "0",  
      "status": "1",  
      "expirationDate": "2017-12-31 12:12:12",  
      "postponedMonth": "0",  
      "maxDelayMonth": "3",  
      "usageCount": "2"  
    },  
    {  
      "iccid": "89860012017300000002",  
      "type": "1",  
      "status": "1",  
      "expirationDate": "2017-12-31 12:12:12",  
      "postponedMonth": "0",  
      "maxDelayMonth": "-1",  
      "usageCount": "2"  
    }  
  \]

}

POST

# **F011-Query order information**

Sales channel can query order-related information via this interface. For this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F011- Query order information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Channel Order ID |

### Request Example

Copy  
{  
  "tradeType": "F011",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "channelOrderId": "2873987483291"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | doc.f011.response.orderId |
| └channelOrderId | String | Y | Channel Order ID |
| └orderStatus | String | Y | Order status: 0- ordered; 1- shipped/delivered; 2- cancelled |
| └courierNumber | String | N | Logistics number |
| └createTime | String | Y | Creation time |
| └express | Object | N | Mailing information |
| └userName | String | Y | Recipient name |
| └userPhone | String | Y | Recipient's phone number |
| └logisticsCompany | String | Y | Logistics company |
| └province | String | Y | Province |
| └city | String | Y | City |
| └district | String | Y | District/ County |
| └address | String | Y | Address |
| └expressFee | String | N | Express fee |
| └selfPickup | Object | N | Self-pick-up information |
| └userName | String | N | Consignee name |
| └userPhone | String | Y | Consignee's phone number |
| └pickupPointId | String | N | ID of self-pick-up site |
| └totalAmount | String | N | Total order amount |
| └discountAmount | String | N | Discount Amount |
| └estimatedUseTime | String | N | Estimated use time |
| └comment | String | N | Remarks |
| └subOrderList | Array | Y | Sub-order set |
| └subOrderId | String | Y | Sub Order ID |
| └channelSubOrderId | String | Y | Channel Sub Order ID |
| └deviceSkuId | String | Y | Card commodity ID |
| └planSkuId | String | N | Plan commodity ID |
| └planSkuCopies | String | N | Copies of plan commodity |
| └number | String | Y | Quantity |
| └ICCID | Array | N | Card number |
| └invoiceType | String | N | Invoice type: 0: individual; 1: company |
| └invoiceHead | String | N | Invoice header |
| └invoiceContent | String | N | Invoice content |
| └invoiceComment | String | N | Invoice notes |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "orderStatus": "0",  
    "courierNumber": "9558213214",  
    "createTime": "2017-08-04 14:20:25",  
    "express": {  
      "userName": "Zhang San",  
      "userPhone": "18610081008",  
      "logisticsCompany": "SF",  
      "province": "Beijing",  
      "city": "Beijing",  
      "district": "Haidian District",  
      "address": "No. 6, Chuangye Road, SHANGDI Information Technology Industry Base",  
      "expressFee": "10"  
    },  
    "totalAmount": "128",  
    "discountAmount": "1",  
    "estimatedUseTime": "2017-12-31",  
    "comment": "S.F. Express pls",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291",  
        "deviceSkuId": "2001",  
        "planSkuId": "1012",  
        "planSkuCopies": "2",  
        "number": "2",  
        "iccid": \[  
          "89860012017300000001",  
          "89860012017300000002"  
        \]  
      },  
      {  
        "subOrderId": "13131313133",  
        "channelSubOrderId": "2873987483292",  
        "deviceSkuId": "2001",  
        "planSkuId": "",  
        "planSkuCopies": "",  
        "iccid": \[  
          "89860012017300000003"  
        \]  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | doc.f011.response.orderId |
| └channelOrderId | String | Y | Channel Order ID |
| └orderStatus | String | Y | Order status: 0- ordered; 1- shipped/delivered; 2- cancelled |
| └createTime | String | Y | Creation time |
| └totalAmount | String | N | Total order amount |
| └discountAmount | String | N | Discount Amount |
| └estimatedUseTime | String | N | Estimated use time |
| └comment | String | N | Remarks |
| └subOrderList | Array | Y | Sub-order set |
| └subOrderId | String | Y | Sub Order ID |
| └channelSubOrderId | String | Y | Channel Sub Order ID |
| └ICCID | Array | Y | Card number |
| └skuId | String | Y | Sku ID |
| └copies | String | Y | Number of plan commodity |
| └invoiceType | String | N | Invoice type: 0: individual; 1: company |
| └invoiceHead | String | N | Invoice header |
| └invoiceContent | String | N | Invoice content |
| └invoiceComment | String | N | Invoice notes |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "orderStatus": "2",  
    "createTime": "2017-08-04 15:20:35",  
    "totalAmount": "128",  
    "comment": "",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291",  
        "iccid": \[  
          "89860012017300000001",  
          "89860012017300000002"  
        \],  
        "skuId": "1273",  
        "copies": "5"  
      },  
      {  
        "subOrderId": "13131313133",  
        "channelSubOrderId": "2873987483292",  
        "iccid": "89860012017300000003",  
        "skuId": "1108",  
        "copies": "1"  
      }  
    \]  
  }

}

POST

# **F012-Query data plan usage information**

The sales channel can query the validity of this iccid card via this interface. For this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F012- Query data plan usage information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └iccid | String | Y | ICCID |
| └channelOrderId | String | N | Channel Order ID |
| └language | String | N | doc.f012.params.language |
| └eid | String | N | EID |

### Request Example

Copy  
{  
  "tradeType": "F012",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "iccid": "1234567890123456789",  
    "language": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └skuName | String | Y | Commodity name |
| └planStartTime | String | Y | Plan start time |
| └planEndTime | String | Y | Plan end time |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": \[  
    {  
      "orderId": "13131313131",  
      "channelOrderId": "138788765467",  
      "subOrderList": \[  
        {  
          "subOrderId": "13131313132",  
          "channelSubOrderId": "2873987483291",  
          "skuId": "1273",  
          "planType": "0",  
          "skuName": "Australia-4G-Optional-300MB",  
          "copies": "5",  
          "planStatus": "2",  
          "planStartTime": "2018-03-04 09:20:35",  
          "planEndTime": "2018-03-09 09:20:35",  
          "totalDays": "5",  
          "country": \[  
            {  
              "mcc": "AU",  
              "name": "Australia",  
              "apn": "emov",  
              "apnType": "0"  
            }  
          \],  
          "highFlowSize": "307200",  
          "apn": "emov",  
          "remainingDays": "0"  
        }  
      \]  
    }  
  \]

}

POST

# **F013-Validate add package iccids**

The sales channel can verify whether iccid can recharge through this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F013- Validate add package iccids |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └iccid | Array | Y | Recharge card number array, no repeated, scope 1-500 |

### Request Example

Copy  
{  
  "tradeType": "F013",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "iccid": \[  
      "1234567890123456789",  
      "1234567890123456790"  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └iccid | String | Y | Card number |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "iccid": "89860012017300000001",  
      "result": "success"  
    },  
    {  
      "iccid": "89860012017300000002",  
      "result": "failed"  
    }  
  \]

}

POST

# **F014-Query sale account balance**

The sales channel can query the pre deposit account balance of its own channel through this interface., the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F014- Query sale account balance |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |

### Request Example

Copy  
{  
  "tradeType": "F014",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {}

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └accountBalance | String | Y | Account balance |
| └currency | String | Y | Currency |
| └availableBalance | String | Y | Available balance |
| └frozenBalance | String | N | Frozen balance |
| └creditLimit | String | N | Credit limit |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "accountBalance": "10000.00",  
    "currency": "USD",  
    "availableBalance": "8500.00",  
    "frozenBalance": "1500.00",  
    "creditLimit": "50000.00"  
  }

}

POST

# **F015-Query acceleration package commodities**

Plans

The sales channel can query the acceleration package commodities through this interface., the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F015- Query acceleration package commodities |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └skuId | String | N | Commodity ID |
| └language | String | N | doc.f015.params.language |

### Request Example

Copy  
{  
  "tradeType": "F015",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "language": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └skuId | String | Y | Commodity ID |
| └name | String | Y | Commodity name |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "orderId": "13131313131",  
      "iccid": "89860012017300000001",  
      "skuId": "1273",  
      "accelerationSku": \[  
        "1274"  
      \],  
      "accelerationSkuList": \[  
        {  
          "skuId": "1274",  
          "settlementPrice": "11",  
          "name": "Hong Kong-4G-1 day-200M",  
          "pointContactType": "1",  
          "serviceZone": "UTC+8",  
          "pointOfContact": "2022-12-02 00:00:00",  
          "days": "1",  
          "capacity": "",  
          "highFlowSize": "20000",  
          "limitFlowSpeed": "128",  
          "hotspotSupport": "1",  
          "country": \[  
            {  
              "mcc": "HK",  
              "name": "Hong Kong"  
            }  
          \]  
        }  
      \]  
    }  
  \]

}

POST

# **F016-Create acceleration package order**

The sales channel can create a acceleration package order through this interface., the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F016- Create acceleration package order |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └totalAmount | String | Y | Total order amount |
| └orderCreateTime | String | Y | Order create time |

### Request Example

Copy  
{  
  "tradeType": "F016",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "channelOrderId": "138788765467",  
    "totalAmount": "100",  
    "orderCreateTime": "2017-12-12 12:12:12"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of channel |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291",  
        "isActivated": "1",  
        "startTime": "2018-08-07 12:12:12",  
        "countryRegion": "HK",  
        "apn": "emov",  
        "apnUsername": "",  
        "apnPassword": ""  
      }  
    \]  
  }

}

POST

# **F017-Apply after sale**

The sales channel can obtain the list of commodities it can sell, with external sales channel application system as the calling party , And the sales channel submit the after sale apply

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F017- Apply after sale |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Channel Order ID |
| └channelSubOrderId | String | Y | the subOrderId of the channel |
| └reason | String | Y | the reason of the after sale(please write in the after sale reason number)20:unjustification,29:not downloaded eSIM cancellation |
| └ICCID | Array | Y | ICCID |
| └unSubscribeFlow | String | N | return flow or not: 0.don\`t return,1.return |
| └receivingState | String | N | receivingState:0.have not received,1.had received |
| └returnCardOrNot | String | N | Return card or not(the order should be incloud card and the receivingState should be 1): 0: don\`t need to return, 2\. need to return |
| └logisticsNoPerson | String | N | the name of the addresser: 0.client, 1.warehouse operator |
| └logisticsId | String | N | tracking number |
| └refundType | String | Y | refund way: 0.automatic refund, 1\. agreement refunds |
| └refundAmount | String | N | refund amount |
| └comment | String | N | Remarks |

### Request Example

Copy  
{  
  "tradeType": "F017",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "channelOrderId": "2873987483291",  
    "channelSubOrderId": "3073987483291",  
    "reason": "20",  
    "iccid": \[  
      "123",  
      "123"  
    \],  
    "unSubscribeFlow": "0",  
    "receivingState": "1",  
    "returnCardOrNot": "1",  
    "logisticsNoPerson": "1",  
    "logisticsId": "1111111111111",  
    "refundType": "1",  
    "refundAmount": "48.00",  
    "comment": ""  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └afterSaleId | String | Y | After Sale ID |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "afterSaleId": "1517370598118598"  
  }

}

POST

# **F018-Cancel after sale**

The sales channel can obtain the list of commodities it can sell, with external sales channel application system as the calling party , And the sale channel can cancel the after sale apply

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F018- Cancel after sale |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └afterSaleId | String | Y | after sale id |

### Request Example

Copy  
{  
  "tradeType": "F018",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "afterSaleId": "1517370598118598"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success"

}

POST

# **F019-Modify after sale**

The sales channel can obtain the list of commodities it can sell, with external sales channel application system as the calling party , And the sales channel can change the after sale apply

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F019- Modify after sale |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └afterSaleId | String | Y | after sale id |
| └unSubscribeFlow | String | N | return flow or not: 0.don\`t return,1.return |
| └receivingState | String | N | receivingState:0.have not received,1.had received |
| └returnCardOrNot | String | N | Return card or not(the order should be incloud card and the receivingState should be 1): 0: don\`t need to return, 2\. need to return |
| └logisticsNoPerson | String | N | the name of the addresser: 0.client, 1.warehouse operator |
| └logisticsId | String | N | tracking number |
| └refundType | String | Y | refund way: 0.automatic refund, 1\. agreement refunds |
| └refundAmount | String | N | refund amount |
| └comment | String | N | doc.f019.params.comment |

### Request Example

Copy  
{  
  "tradeType": "F019",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "afterSaleId": "2873987483291",  
    "unSubscribeFlow": "1",  
    "receivingState": "1",  
    "returnCardOrNot": "1",  
    "logisticsNoPerson": "1",  
    "logisticsId": "1111111111111",  
    "refundType": "1",  
    "refundAmount": "48.00",  
    "comment": ""  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success"

}

POST

# **F020-Query after sale information**

The flow platform sends order delivery notice to the sale channel, with flow platform as the calling party And the sale channel can query the after sale message by some qualifications

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F020- Query after sale |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └afterSaleId | String | Y | after sale id |

### Request Example

Copy  
{  
  "tradeType": "F020",  
  "tradeTime": "2017-12-12 12:12:12",  
  "tradeData": {  
    "afterSaleId": "1517370598118598"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Channel Order ID |
| └channelSubOrderId | String | N | Channel Sub Order ID |
| └afterSaleId | String | Y | after sale id |
| └ICCID | Array | Y | card numbers |
| └reason | String | Y | Reason |
| └refundType | String | Y | Refund Type |
| └refundAmount | String | Y | Refund Amount |
| └unSubscribeFlow | String | N | Unsubscribe Flow |
| └returnDays | String | N | Return Days |
| └receivingState | String | N | Receiving State |
| └returnCard | String | N | return card or not(just when the order was include card order and the receivingState was 1\) : 0.don\`t need, 1.need to return card |
| └logisticsNoPerson | String | N | Logistics No Person |
| └logisticsId | String | N | Logistics ID |
| └auditStatus | String | Y | audit status:0：not audit，1：has withdraw 2：audit pass 3：rejected 4：need modify |
| └auditOpinion | String | N | audit opinion |
| └refundStatus | String | Y | refund status 0、waiting refund，1、refunded，2、rejected |
| └refundOpinion | String | N | refund opinion |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "channelOrderId": "1517370598118598",  
    "channelSubOrderId": "1517370598118598",  
    "afterSaleId": "1517370598118598",  
    "iccid": \[  
      "89860012011111111111"  
    \],  
    "reason": "14",  
    "refundType": "1",  
    "refundAmount": "123.00",  
    "unSubscribeFlow": "1",  
    "returnDays": "1",  
    "receivingState": "1",  
    "returnCard": "1",  
    "logisticsNoPerson": "1",  
    "logisticsId": "11111111111",  
    "auditStatus": "1",  
    "auditOpinion": "XXXXXXX",  
    "refundStatus": "1",  
    "refundOpinion": "XXXXXXX"  
  }

}

POST

# **F023-Daily flow query**

The sales channel can query the daily flow of the card through the interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F023- Daily flow query |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └ICCID | String | Y | ICCID |
| └beginDate | String | Y | Begin date |
| └endDate | String | Y | End date |
| └tzType | String | N | timezone,default 1 |
| └language | String | N | doc.f023.params.language |

### Request Example

Copy  
{  
  "tradeType": "F023",  
  "tradeTime": "2018-09-10 12:12:12",  
  "tradeData": {  
    "iccid": "89860012017300000001",  
    "beginDate": "2018-10-01",  
    "endDate": "2018-10-01",  
    "tzType": "1",  
    "language": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └usedDate | String | Y | Used date |
| └type | String | Y | Business type: 0-Data 1-SMS 2-USSD 3-LU |
| └usedAmount | String | Y | Amount of use (busiType \= 0, unit: KB; other units: bar) |
| └country | String | Y | Country |
| └countryRegionCode | String | Y | Country region code |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "usedDate": "20181001",  
      "type": "0",  
      "usedAmount": "250770",  
      "country": "Hong Kong (People's Republic of China)",  
      "countryRegionCode": "HK"  
    }  
  \]

}

POST

# **F040-Create eSIM order**

eSIM Order

The user creates eSIM order, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F040- Create eSIM order |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └email | String | N | Email |
| └totalAmount | String | N | Total order amount |
| └discountAmount | String | N | Discount amount |
| └estimatedUseTime | String | N | Estimated use time |
| └orderCreateTime | String | N | Order create time |
| └comment | String | N | Remarks |
| └subOrderList | Array | Y | Sub-order set |
| └channelSubOrderId | String | Y | Sub-order ID of sales channel |
| └deviceSkuId | String | Y | eSIM commodity ID |
| └deviceSkuPrice | String | N | Card commodity price |
| └planSkuCopies | String | Y | Number of plan commodity |
| └number | String | Y | Purchase quantity |
| └discountAmount | String | N | Discount amount |
| └rechargeableESIM | String | N | Rechargeable eSIM |
| └invoiceType | String | N | Invoice type: 0: individual; 1: company |
| └invoiceHead | String | N | Invoice header |
| └invoiceContent | String | N | Invoice content |
| └invoiceComment | String | N | Invoice notes |
| └userId | String | N | Submitter |
| └eid | String | N | EID |
| └imei | String | N | IMEI 2 |

### Request Example

Copy  
{  
  "tradeType": "F040",  
  "tradeTime": "2020-02-25 15:02:21",  
  "tradeData": {  
    "channelOrderId": "138788765467",  
    "email": "abc@qq.com",  
    "totalAmount": "128",  
    "discountAmount": "1",  
    "subOrderList": \[  
      {  
        "channelSubOrderId": "2873987483292",  
        "deviceSkuId": "1535444366670209",  
        "planSkuCopies": "1",  
        "number": "1"  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | doc.f040.response.channelOrderIdResponse |
| └subOrderList | Array | Y | doc.f040.response.subOrderListResponse |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | doc.f040.response.channelSubOrderIdResponse |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "Success",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "2873987483291"  
      }  
    \]  
  }

}

POST

# **F041-Resend eSIM email**

The user resend eSIM email, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F041- Resend eSIM email |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └email | String | Y | Email |

### Request Example

Copy  
{  
  "tradeType": "F041",  
  "tradeTime": "2020-02-25 16:05:29",  
  "tradeData": {  
    "orderId": "15801188888",  
    "email": "abc@qq.com"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **F042-Query eSIM profile status**

The user query eSIM profile status, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F042- Query eSIM profile status |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └ICCID | String | Y | ICCID |

### Request Example

Copy  
{  
  "tradeType": "F042",  
  "tradeTime": "2022-12-07 15:34:16",  
  "tradeData": {  
    "iccid": "89860012016820003006"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └ICCID | String | Y | ICCID |
| └status | String | Y | Status：0-not downloaded 1-downloaded 2-installed 3-enabled 4-disabled 5-recycled |
| └recordTime | String | Y | Record time |
| └eid | String | N | Return the specific eid value for the installed eSIM |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "status": "5",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-24 10:32:47",  
      "orderId": "3669195566166268",  
      "eid": "89086030202200000020000002270960"  
    },  
    {  
      "status": "4",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-24 10:32:38",  
      "orderId": "3669195566166268",  
      "eid": "89086030202200000020000002270960"  
    },  
    {  
      "status": "3",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-23 17:28:45",  
      "orderId": "3669195566166268",  
      "eid": "89086030202200000020000002270960"  
    },  
    {  
      "status": "2",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-23 17:27:04",  
      "orderId": "3669195566166268",  
      "eid": "89086030202200000020000002270960"  
    },  
    {  
      "status": "1",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-23 17:26:19",  
      "orderId": "3669195566166268",  
      "eid": ""  
    },  
    {  
      "status": "0",  
      "iccid": "89860012016820003006",  
      "recordTime": "2022-11-23 17:26:31",  
      "orderId": "3669195566166268",  
      "eid": ""  
    }  
  \]

}

POST

# **F045-Terminate active plan**

The sales channel can terminate active plan through this interface., the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F045- Terminate active plan |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Order ID |
| └subOrderId | String | Y | Sub Order ID |
| └ICCID | String | Y | ICCID |

### Request Example

Copy  
{  
  "tradeType": "F045",  
  "tradeTime": "2023-05-05 09:47:39",  
  "tradeData": {  
    "orderId": "2683536856139402",  
    "subOrderId": "1683536856470403",  
    "iccid": "89860012018390075038"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **F046-Query data plan usage information**

The sales channel can query the usage information through this interface. For this interface, the external sales channel application system acts as the calling party and the flow platform acts as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F046- Query data plan usage information |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | N | Main order ID of flow platform, At least one of the orderId or channelOrderId must be passed |
| └channelOrderId | String | N | Main order ID of channel to be queried, At least one of the orderId or channelOrderId must be passed |
| └ICCID | String | Y | ICCID |
| └language | String | N | doc.f046.params.language |

### Request Example

Copy  
{  
  "tradeType": "F046",  
  "tradeTime": "2023-05-30 15:53:32",  
  "tradeData": {  
    "orderId": "2684910712887645",  
    "channelOrderId": "71186072946800",  
    "iccid": "89860012018880000641",  
    "language": "2"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └orderId | String | Y | Order ID |
| └channelOrderId | String | Y | Channel Order ID |
| └subOrderList | Array | Y | Sub Orders List |
| └subOrderId | String | Y | Sub Order ID |
| └channelSubOrderId | String | Y | Channel Sub Order ID |
| └skuId | String | Y | Sku ID |
| └skuName | String | Y | Commodity name |
| └copies | String | Y | Copies |
| └planStatus | String | Y | Flow use status: 0- not used; 1- in use; 2- used; 3- cancelled |
| └planStartTime | String | N | Plan start time |
| └planEndTime | String | N | Plan end time |
| └totalDays | String | N | Total Days |
| └totalTraffic | String | N | Total Traffic |
| └usageInfoList | Array | N | Usage info |
| └usedDate | String | Y | Used time |
| └usageAmt | String | Y | Used data traffic, unit: KB |
| └highFlowSize | String | N | High Flow Size |
| └planType | String | N | Plan Type |
| └country | Array | N | Country |
| └mcc | String | Y | Country Mcc |
| └name | String | Y | Country Name |
| └apn | String | Y | Country Apn |
| └apnUsername | String | N | Apn Username |
| └apnPassword | String | N | Apn Password |
| └apnType | String | Y | Apn Type |
| └authenticationType | String | N | Authentication Type |
| └apnTypeDesc | String | N | Apn Type Desc |
| └operator | String | N | Operator |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": {  
    "orderId": "2684910712887645",  
    "channelOrderId": "71186072946800",  
    "subOrderList": \[  
      {  
        "subOrderId": "1684910712888646",  
        "channelSubOrderId": "21186072505668",  
        "skuId": "1683599122978282",  
        "skuName": "Japan-4G-300MB/day+tonguxnzaiti",  
        "copies": "2",  
        "planStatus": "2",  
        "planStartTime": "2023-05-24 14:59:36",  
        "planEndTime": "2023-05-26 14:59:37",  
        "totalDays": "2",  
        "totalTraffic": "-1",  
        "highFlowSize": "307200",  
        "planType": "1",  
        "country": \[  
          {  
            "mcc": "JP",  
            "name": "Japan",  
            "apn": "3gnet",  
            "apnUsername": "test",  
            "apnPassword": "test",  
            "apnType": "1",  
            "operator": "SoftBank"  
          }  
        \],  
        "usageInfoList": \[  
          {  
            "usedDate": "20230524",  
            "usageAmt": "10240"  
          },  
          {  
            "usedDate": "20230525",  
            "usageAmt": "20480"  
          },  
          {  
            "usedDate": "20230526",  
            "usageAmt": "30760"  
          }  
        \]  
      }  
    \]  
  }

}

POST

# **F051-Query self pickup information by sku**

Query corresponding pickup point information based on commodity ID, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F051- Query self pickup information by sku |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └skuIds | Array | Y | Commodity IDs |

### Request Example

Copy  
{  
  "tradeType": "F051",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "skuIds": \[  
      "123456",  
      "123457"  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └pointId | String | Y | Self-pick-up site ID |
| └address | String | Y | Address |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "pointId": "101",  
      "address": "Area B International Departure Layer, Guangzhou Baiyun International Airport",  
      "openingHours": "7\*24 hours",  
      "gpsInfo": "121.8035020000,31.1489150000",  
      "contactWay": "Tel.:18623123020"  
    }  
  \]

}

POST

# **F052-Query eSIM recharge plans**

Plans

The sales channel can query eSIM recharge plans, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F052- Query eSIM recharge plans |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └ICCID | String | Y | ICCID |

### Request Example

Copy  
{  
  "tradeType": "F052",  
  "tradeTime": "2024-08-14 15:33:24",  
  "tradeData": {  
    "iccid": "89812003916820397415"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └skuId | Array | Y | Commodity ID |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": {  
    "skuId": \[  
      "132342909036",  
      "132346328450"  
    \]  
  }

}

POST

# **F054-Query realname authentication status**

The sales channel can query realname authentication status, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F054- Query realname authentication status |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └ICCID | String | Y | ICCID |

### Request Example

Copy  
{  
  "tradeType": "F054",  
  "tradeTime": "2024-11-11 12:01:54",  
  "tradeData": {  
    "iccid": "89812003916820397415"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Object | N | Definitions are shown in the table below |
| └status | Integer | Y | Authentication status：1-Pending Verification 2-In Verification 3-Verification Approved 4-Verification Failed 5-Document Expired |
| └expiryTime | String | N | Authentication expiration date |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": {  
    "status": 1  
  }

}

POST

# **F056-Query all acceleration package commodities**

Plans

Get a list of all available acceleration package commodities, with external sales channel application system as the calling party and the flow platform as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | F056- Query all acceleration package commodities |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └skuId | String | N | Commodity ID |
| └language | String | N | doc.f056.params.language |

### Request Example

Copy  
{  
  "tradeType": "F056",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "language": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |
| tradeData | Array | N | Definitions are shown in the table below |
| └skuId | String | Y | Commodity ID |
| └name | String | Y | Commodity name |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success",  
  "tradeData": \[  
    {  
      "skuId": "1274",  
      "settlementPrice": "11",  
      "name": "Hong Kong-4G-1 day-200M",  
      "pointContactType": "1",  
      "serviceZone": "UTC+8",  
      "pointOfContact": "2022-12-02 00:00:00",  
      "days": "1",  
      "capacity": "",  
      "highFlowSize": "20000",  
      "limitFlowSpeed": "128",  
      "hotspotSupport": "1",  
      "country": \[  
        {  
          "mcc": "HK",  
          "name": "Hong Kong"  
        }  
      \]  
    }  
  \]

}

POST

# **N001-Order delivery notice**

The flow platform sends order delivery notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N001- Order delivery notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └orderStatus | String | Y | Order status: 0- ordered; 1- shipped/delivered; 2- cancelled |

### Request Example

Copy  
{  
  "tradeType": "N001",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "orderStatus": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N002-Plan use start notice**

The flow platform sends plan use start notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N002- Plan use start notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └planId | String | Y | Plan ID |
| └planStatus | String | Y | Plan Status |

### Request Example

Copy  
{  
  "tradeType": "N002",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderId": "13131313132",  
    "channelSubOrderId": "138788765468",  
    "planId": "1273",  
    "planStatus": "2"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N003-Plan use end notice**

The flow platform sends plan use end notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N003- Plan use end notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └ICCID | String | Y | ICCID |
| └shipStatus | String | Y | Ship Status |

### Request Example

Copy  
{  
  "tradeType": "N003",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderId": "13131313132",  
    "channelSubOrderId": "138788765468",  
    "ICCID": "89860012018500000085",  
    "shipStatus": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N004-After sale audit notice**

The flow platform sends after sale audit notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N004- After sale audit notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └afterSaleId | String | Y | after sale id |
| └auditStatus | String | Y | after sale state:1.success, 2.false |
| └auditOpinion | String | N | audit opinion |

### Request Example

Copy  
{  
  "tradeType": "N004",  
  "tradeTime": "2017-02-10 11:11:11",  
  "tradeData": {  
    "afterSaleId": "2873987483290",  
    "auditStatus": "1",  
    "auditOpinion": "同意"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N005-Refund notice**

The flow platform sends refund notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N005- Refund notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └ICCID | String | Y | ICCID |
| └targetStatus | String | Y | Target Status |

### Request Example

Copy  
{  
  "tradeType": "N005",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderId": "13131313132",  
    "channelSubOrderId": "138788765468",  
    "ICCID": "89860012018500000085",  
    "targetStatus": "1"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N006-Card status change notice**

The flow platform sends card status change notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N006- Card status change notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └ICCID | String | Y | ICCID |
| └planId | String | Y | Plan ID |
| └planExpiredTime | String | Y | Plan Expired Time |

### Request Example

Copy  
{  
  "tradeType": "N006",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderId": "13131313132",  
    "channelSubOrderId": "138788765468",  
    "ICCID": "89860012018500000085",  
    "planId": "1273",  
    "planExpiredTime": "2024-12-31 23:59:59"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N009-eSIM QR code notice**

eSIM Order

The flow platform sends order delivery notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N009- eSIM QR code notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderList | Array | Y | Sub-order set |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └uid | String | Y | uid |
| └ICCID | String | Y | ICCID |
| └qrCodeContent | String | Y | Qr code content |
| └apn | String | Y | apn |
| └apnUsername | String | N | apn username |
| └apnPassword | String | N | apn password |
| └pin | String | N | Pin |
| └puk | String | N | Puk |
| └msisdn | String | N | msisdn |
| └validTime | String | N | Valid time |
| └rechargeableESIM | String | Y | Rechargeable eSIM |

### Request Example

Copy  
{  
  "tradeType": "N009",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "13131",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "131",  
        "uid": "18500000085",  
        "iccid": "89860012018500000085",  
        "qrCodeContent": "LPA:1$SECSMSMINIAPP.EASTCOMPEACE.COM$2C13942911FF452AB45E9E99A5D444A1",  
        "apn": "emov",  
        "pin": "1234",  
        "puk": "55026381",  
        "msisdn": "",  
        "validTime": "2023-09-23 17:58:07",  
        "rechargeableESIM": "0"  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N010-eSIM email send notice**

The flow platform sends eSIM email notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N010- eSIM email send notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |

### Request Example

Copy  
{  
  "tradeType": "N010",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N012-eSIM profile status notice**

The flow platform sends eSIM profile status notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N012- eSIM profile status notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderList | Array | Y | Sub-order set |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └uid | String | Y | uid |
| └ICCID | String | Y | ICCID |
| └profileStatus | Integer | Y | Profile Status：0-undownload 1-downloaded 2-installed 3-enabled 4-disabled 5-deleted |

### Request Example

Copy  
{  
  "tradeType": "N012",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "13131",  
    "subOrderList": \[  
      {  
        "subOrderId": "13131313132",  
        "channelSubOrderId": "131",  
        "uid": "18500000085",  
        "iccid": "89860012018500000085",  
        "profileStatus": 1  
      }  
    \]  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}

POST

# **N013-Top-up order result notice**

The flow platform sends top-up order result notice to the sale channel, with flow platform as the calling party and the external sales channel as the called party.

### Request Parameters

| Parameter | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeType | String | Y | N013- Top-up order result notice |
| tradeTime | String | Y | Communication time stamp |
| tradeData | Object | Y | Definitions are shown in the table below |
| └orderId | String | Y | Main order ID of flow platform |
| └channelOrderId | String | Y | Main order ID of sales channel |
| └subOrderId | String | Y | Sub-order ID of flow platform |
| └channelSubOrderId | String | Y | Main and sub-order ID of channel |
| └ICCID | String | Y | ICCID |
| └planId | String | Y | Plan ID |
| └planActiveTime | String | Y | Plan Active Time |

### Request Example

Copy  
{  
  "tradeType": "N013",  
  "tradeTime": "2020-02-25 16:56:37",  
  "tradeData": {  
    "orderId": "13131313131",  
    "channelOrderId": "138788765467",  
    "subOrderId": "13131313132",  
    "channelSubOrderId": "138788765468",  
    "ICCID": "89860012018500000085",  
    "planId": "1273",  
    "planActiveTime": "2024-01-01 10:00:00"  
  }

}

### Response Fields

| Field | Type | Required | Description |
| :---- | :---- | :---- | :---- |
| tradeCode | String | Y | Interactive result error code, 1000 for success and all others for failure |
| tradeMsg | String | Y | Interactive result description |

### Response Example

Copy  
{  
  "tradeCode": "1000",  
  "tradeMsg": "success"

}  
