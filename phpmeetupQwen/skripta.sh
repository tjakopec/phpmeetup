#!/bin/bash

# Definiramo datoteke/mape koje želimo ignorirati
IGNORE="vendor|.git|.idea|.vscode|var|tests|composer.lock|Plan.txt|Task.md|sadrzaj.txt|.DS_Store|skripta.sh"

# Inicijaliziramo datoteku (stvara novu ili briše staru)
echo "--- SADRŽAJ DATOTEKA ---" > sadrzaj.txt

# Pronalazimo sve datoteke (osim onih koje ignoriramo)
find . -type f | grep -Ev "$IGNORE" | while read -r file; do
    echo "========================================" >> sadrzaj.txt
    echo "FILE: $file" >> sadrzaj.txt
    echo "========================================" >> sadrzaj.txt
    cat "$file" >> sadrzaj.txt
    echo -e "\n" >> sadrzaj.txt
done