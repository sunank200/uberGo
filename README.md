# uberGo

    This is an web app  for Uber ride remainder where you take 4 inputs - Source, Destination, time of the day to reach the destination and Email Address. Assume that source and destination are provided as lat/long and not as addresses(to keep things simple). The app finds the exact time a user needs to book an uber to be at that destination at that time. An email needs to be sent to the above email ID at this time saying “Time to book an uber!”

    Example: I am in Koramangala(12.927880, 77.627600) and need to be in Hebbal(13.035542, 77.597100) at 8:00 PM for a meeting. The app will have to email me at 6:43 PM because the uberGO will take 9 mins to reach me at 6:43 PM(as per uber) and it takes 68 mins to drive from Koramangala to Hebbal(as per google maps)

    Assumptions:
    - To keep things simple, assumed that whatever the uber api and google maps api tell you i s true.
    - Assumed that the time to arrive at the destination is within today.  Assumed that the time entered is IST.
    - Assumed that the maximum deviation of driving times at any time of the day is 60 mins. That means, if it takes 40 mins to drive from Koramangala to Hebbal now, you can assume that it’s not more than 100 mins at any time of the day.
    - The cab is UberGO.

     Accuracy is the most important factor to judge this app but the second important factor is optimization of requests to the APIs. Pinging the APIs every minute to see if you should leave now is not the best solution.
