# Moodle plugin: HarpIA Ajax

HarpIA Ajax is a Moodle plugin that implements
an AJAX interaction with an arbitrary answer provider,
such as an external language model. 

The actual calls to the language models are
performed on the server.
Currently, the plugin sends the requests
to a (usually local) HTTP server that implements a simple API
demonstrated in the examples below
(change the host and port accordingly).

### Requesting the list of answer providers

```http
# Requesting list of providers:
GET http://localhost:42774/list
```

Response:

```json
{
    "providers": [
        "name of the first provider",
        "name of the second provider",
        "name of the third provider",
    ]   
}

```

### Requesting an answer

```http
# Requesting list of providers:
POST http://localhost:42774/send
Content-Type: application/json

{
    "query": "the query",
    "answer_provider": "name of the requested provider"
}
```

Response:

```json
{
    "text": "the answer",
    "interaction_id": "id_of_this_interaction"
}

```


### Current implementations of answer providers

The repository [TODO: add link] contains an implementation of a few
answer providers, such as:
- GPT models (via OpenAI API);
- constant utterances (for quick testing purposes).
