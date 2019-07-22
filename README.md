# BreakTheRules Backend

This is the official repo for the UCT Developer Society *\#BreakTheRules* backend.

## API Documentation

### Baseline

We tried to make the API as REST-compliant as possible. Here is the baseline information on how the API works.

All queries must have the following format:

    https://backend.breaktherules.co.za/[path]
    
- Use `https`.
- Use the default port (443).
- If doing non-GET requests, body must be in json. Include `Content-Type: application/json` in the headers.
- Options specified with GET requests should be in the query params (not body).
- Responses will always be in JSON.
- DO NOT rerun non-GET requests unless the user requests it.
- If something goes wrong the server will respond with a descriptive message. The HTTP response code also varies depending on how the query ran. Generally:
    - `200`: Everything is okay.
    - `201`: Entity created.
    - `400`: Bad request. Check format.
    - `401`: Unauthorised. Check that auth is correct.
    - `409`: Conflict. The thing you are trying to create already exists (eg student already registered).
    - `500`: Something went wrong server-side. This should only happen while in development, but it's good practice to handle your edge cases.
- During development, there will be very little backend input-checking. Please make sure the requests are formatted perfectly and the request body contains **ALL** the required parameters. Cowboy-coding could lead to incomplete transactions.

### Authorisation

For the time being, only the official client may make requests to the backend. To ensure this, I have devised a rotating auth-token system. Essentially both the client and the server will have a pre-shared key which the client will encode and use as auth token with each requests. The server will do a similar encoding and check if the auths match. The encoding algorithm I created works in such a way that the token will be valid for a 20-second interval only. [Contact me](mailto:george@rauten.co.za) for the algorithm code or key.

Include the following header in all requests:

    Auth: <token>
    
*(Not strictly REST standard; it's a long story.)*

Test whether you can make requests by requesting to root (/). You should get a `200` response if all is good.

### Endpoints

#### POST /student

Register a student.

Request body:

- `stuno`: string. The student number
- `pref_name`: string. The student's preferred name (This will be printed on their badge at the event).
- `surname`: string. The student's surname.
- `entry_year`: int. The year the student started studying their current degree. This will be used to calculate what year they are in.
- `program_length`: int. The number of years the student's current degree program is.
- `faculty`: string. The faculty the student is registered in. For data integrity, please give the user a list to pick out of.
- `majors`: array of strings. The majors the student is registered for. Please give the user a list to pick from and only allow "other" if absolutely necessary. Later I'll make an endpoint to get a list of all registered majors.

Example request:

    {
      "stuno": "ABCDEF001",
      "pref_name": "John",
      "surname": "McLennon",
      "entry_year": 2017,
      "program_length": 4,
      "faculty": "Science",
      "majors": [
        "Computer Science", "Computer Engineering"
      ]
    }

