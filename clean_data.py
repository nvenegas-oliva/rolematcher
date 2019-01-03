import pandas as pd

df = pd.read_csv('query_result.csv')
df['po_career'] = df['po_career'].astype(str)
df.info()

df.head(15)

rules = {
    "&": "and"
}

for old, new in rules.items():
    df['new'] = df['po_career'].apply(lambda x: x.replace(old, new))


df.head(30)
