import pandas as pd
from difflib import get_close_matches

roles = pd.read_csv('roles_to_apply.csv', dtype={'name': str})
df = pd.read_csv('potential-po_career.csv', dtype={'po_career': str})

# Build a unique list of roles removing "composed" roles (|##|)
to_clean = []
for key, row in df.iterrows():
    if isinstance(row['po_career'], float):
        continue
    if "|##|" in row['po_career']:
        to_clean.extend(list(map(str.strip, row['po_career'].split("|##|"))))
    else:
        to_clean.append(row['po_career'].strip())

to_clean = list(set(to_clean))


# Create a list of transformation rules
rules = []
for role in to_clean:
    suggestions = get_close_matches(role, roles['name'])
    response = input("role='%s' -- suggested=%s" % (role, suggestions))
    if response == "":
        rules.append({
            "rule-key": role,
            "rule-value": suggestions[0],
            "type": "automatic"
        })
    elif response.isdigit():
        rules.append({
            "rule-key": role,
            "rule-value": suggestions[int(response)],
            "type": "pseudo-automatic"
        })
    else:
        rules.append({
            "rule-key": role,
            "rule-value": response,
            "type": "manual"
        })


rules = pd.DataFrame(rules)
rules.to_csv("rules.csv", index=False)
