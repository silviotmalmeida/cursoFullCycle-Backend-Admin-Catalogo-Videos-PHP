#!/bin/bash

echo "Enviando mensagem UDP..."
echo "test" | nc -u -w0 127.0.0.1 4718